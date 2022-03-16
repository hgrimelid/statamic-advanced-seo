<?php

namespace Aerni\AdvancedSeo\Content;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\AssetContainer;

class SocialImage
{
    public function __construct(protected Entry $entry, protected array $specs)
    {
        //
    }

    public function generate(): array
    {
        $this->ensureDirectoryExists();

        Browsershot::url($this->templateUrl())
            ->windowSize($this->specs['width'], $this->specs['height'])
            ->save($this->absolutePath());

        return [$this->specs['field'] => $this->path()];
    }

    public function toFieldtypeArray(): array
    {
        return [
            'width' => $this->specs['width'],
            'height' => $this->specs['height'],
            'url' => $this->templateUrl(),
        ];
    }

    protected function templateUrl(): string
    {
        return "{$this->entry->site()->absoluteUrl()}/social-images/{$this->specs['type']}/{$this->entry->id()}";
    }

    protected function path(): string
    {
        return "social_images/{$this->entry->slug()}-{$this->entry->locale()}-{$this->specs['type']}.png";
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
