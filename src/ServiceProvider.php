<?php

namespace Aerni\AdvancedSeo;

use Aerni\AdvancedSeo\Data\SeoVariables;
use Aerni\AdvancedSeo\Facades\Defaults;
use Aerni\AdvancedSeo\Stache\SeoStore;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Git;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

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
            $nav->tools('SEO')
                ->can('index', SeoVariables::class)
                ->route('advanced-seo.index')
                ->icon('seo-search-graph')
                ->active('advanced-seo')
                ->children([
                    $nav->item('Site Defaults')
                        ->route('advanced-seo.show', 'site')
                        ->can('siteDefaultsIndex', SeoVariables::class),
                    $nav->item('Content Defaults')
                        ->route('advanced-seo.show', 'content')
                        ->can('contentDefaultsIndex', SeoVariables::class),
                ]);
        });

        return $this;
    }

    protected function bootAddonPermissions(): self
    {
        Permission::group('advanced-seo', 'Advanced SEO', function () {
            Permission::register('view site defaults', function ($permission) {
                $permission
                    ->label('View Site Defaults')
                    ->children([
                        Permission::make('view {group} defaults')
                            ->label('View :group Defaults')
                            ->replacements('group', function () {
                                return Defaults::site()->map(function ($item) {
                                    return [
                                        'value' => $item['handle'],
                                        'label' => $item['title'],
                                    ];
                                });
                            })
                            ->children([
                                Permission::make('edit {group} defaults')
                                    ->label('Edit :group Defaults'),
                            ]),
                    ]);
            });

            Permission::register('view content defaults', function ($permission) {
                $permission
                    ->label('View Content Defaults')
                    ->children([
                        Permission::make('view {group} defaults')
                            ->label('View :group Defaults')
                            ->replacements('group', function () {
                                return Defaults::content()->map(function ($item) {
                                    return [
                                        'value' => $item['handle'],
                                        'label' => $item['title'],
                                    ];
                                });
                            })
                            ->children([
                                Permission::make('edit {group} defaults')
                                    ->label('Edit :group Defaults'),
                            ]),
                    ]);
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
