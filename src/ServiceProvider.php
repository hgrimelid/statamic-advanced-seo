<?php

namespace Aerni\AdvancedSeo;

use Statamic\Statamic;
use Edalzell\Forma\Forma;
use Statamic\Facades\Git;
use Statamic\Stache\Stache;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Aerni\AdvancedSeo\Models\Defaults;
use Aerni\AdvancedSeo\Stache\SeoStore;
use Aerni\AdvancedSeo\Data\SeoVariables;
use Statamic\Providers\AddonServiceProvider;
use Aerni\AdvancedSeo\Http\Controllers\Cp\ConfigController;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\GenerateSocialImages::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\SocialImagesPreviewFieldtype::class,
        Fieldtypes\SourceFieldtype::class,
    ];

    // protected $listen = [
    //     \Aerni\AdvancedSeo\Events\SeoDefaultSetSaved::class => [
    //         \Aerni\AdvancedSeo\Listeners\GenerateFavicons::class,
    //     ],
    // ];

    protected $subscribe = [
        'Aerni\AdvancedSeo\Subscribers\ContentDefaultsSubscriber',
        'Aerni\AdvancedSeo\Subscribers\OnPageSeoBlueprintSubscriber',
        'Aerni\AdvancedSeo\Subscribers\SitemapCacheSubscriber',
        'Aerni\AdvancedSeo\Subscribers\SocialImagesGeneratorSubscriber',
    ];

    protected $tags = [
        Tags\AdvancedSeoTags::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/dist/css/cp.css',
    ];

    protected $policies = [
        \Aerni\AdvancedSeo\Data\SeoVariables::class => \Aerni\AdvancedSeo\Policies\SeoVariablesPolicy::class,
    ];

    public $singletons = [
        \Aerni\AdvancedSeo\Contracts\SeoDefaultsRepository::class => \Aerni\AdvancedSeo\Stache\SeoDefaultsRepository::class,
    ];

    public function bootAddon(): void
    {
        $this
            ->bootAddonStores()
            ->bootAddonNav()
            ->bootAddonPermissions()
            ->bootGit()
            ->autoPublishConfig();

        Forma::add('aerni/advanced-seo', ConfigController::class);
    }

    protected function bootAddonStores(): self
    {
        $seoStore = app(SeoStore::class)->directory(base_path('content/seo'));

        app(Stache::class)->registerStore($seoStore);

        return $this;
    }

    protected function bootAddonNav(): self
    {
        Nav::extend(function ($nav) {
            Defaults::enabled()->groupBy('type')->each(function ($items, $type) use ($nav) {
                $nav->create(ucfirst($type))
                    ->section('SEO')
                    ->can('index', [SeoVariables::class, $type])
                    ->route("advanced-seo.{$type}.index")
                    ->active("advanced-seo/{$type}")
                    ->icon($items->first()['type_icon'])
                    ->children(
                        $items->map(function ($item) use ($nav, $type) {
                            return $nav->item($item['title'])
                                ->can('view', [SeoVariables::class, $item['handle']])
                                ->route("advanced-seo.{$item['type']}.edit", $item['handle'])
                                ->active("advanced-seo/{$type}/{$item['handle']}");
                        })->toArray()
                    );
            });
        });

        return $this;
    }

    protected function bootAddonPermissions(): self
    {
        Permission::group('advanced-seo', 'Advanced SEO', function () {
            Defaults::enabled()->groupBy('type')->each(function ($items, $group) {
                Permission::register("view seo {$group} defaults", function ($permission) use ($group, $items) {
                    $permission
                        ->label('View ' . ucfirst($group))
                        ->children([
                            Permission::make('view seo {group} defaults')
                                ->label('View :group')
                                ->replacements('group', function () use ($items) {
                                    return $items->map(function ($item) {
                                        return [
                                            'value' => $item['handle'],
                                            'label' => $item['title'],
                                        ];
                                    });
                                })
                                ->children([
                                    Permission::make('edit seo {group} defaults')
                                        ->label('Edit :group'),
                                ]),
                        ]);
                });
            });
        });

        return $this;
    }

    protected function bootGit(): self
    {
        if (config('statamic.git.enabled')) {
            Git::listen(\Aerni\AdvancedSeo\Events\SeoDefaultSetSaved::class);
        }

        return $this;
    }

    protected function autoPublishConfig(): self
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'advanced-seo-config',
            ]);
        });

        return $this;
    }
}
