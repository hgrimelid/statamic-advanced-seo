<?php

namespace Aerni\AdvancedSeo\Content;

use Statamic\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\File;
use Statamic\Facades\AssetContainer;
use Statamic\Contracts\Entries\Entry;
use Aerni\AdvancedSeo\Facades\SocialImage as SocialImageApi;

class SocialImage
{
    public function __construct(protected Entry $entry, protected array $model)
    {
        //
    }

    public function generate(): self
    {
        $this->ensureDirectoryExists();

        Browsershot::url($this->templateUrl())
            ->windowSize($this->model['width'], $this->model['height'])
            ->save($this->absolutePath());

        return $this;
    }

    public function exists(): bool
    {
        return File::exists($this->absolutePath());
    }

    public function delete(): bool
    {
        return File::delete($this->absolutePath());
    }

    public function absoluteUrl(): string
    {
        $container = config('advanced-seo.social_images.container', 'assets');

        return URL::assemble(AssetContainer::find($container)->absoluteUrl(), $this->path());
    }

    public function path(): string
    {
        $id = Str::replace('_', '-', $this->model['group']);

        return "social_images/{$this->entry->collection}/{$this->entry->slug}-{$this->entry->locale}-{$id}.png";
    }

    protected function templateUrl(): string
    {
        return url('/') . SocialImageApi::route(
            theme: $this->entry->seo_social_images_theme,
            type: $this->model['type'],
            id: $this->entry->id,
        );
    }

    protected function absolutePath($path = null): string
    {
        $container = config('advanced-seo.social_images.container', 'assets');

        return AssetContainer::find($container)->disk()->path($path ?? $this->path());
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->absolutePath(pathinfo($this->path(), PATHINFO_DIRNAME));

        File::ensureDirectoryExists($directory);
    }
}
