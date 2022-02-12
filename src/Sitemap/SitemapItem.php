<?php

namespace Aerni\AdvancedSeo\Sitemap;

use Aerni\AdvancedSeo\Data\SeoVariables;
use Aerni\AdvancedSeo\Facades\Seo;
use Aerni\AdvancedSeo\Fields\ContentDefaultsFields;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Site;
use Statamic\Taxonomies\LocalizedTerm;

class SitemapItem
{
    protected ?SeoVariables $defaults;

    public function __construct(protected Entry|Taxonomy|LocalizedTerm $content, protected string $site)
    {
        /**
         * The `absoluteUrl` method in the Taxonomy class always takes the current site as basis.
         * In order to get the localized URL, we need to set the correct site for this request.
         */
        if ($content instanceof Taxonomy) {
            Site::setCurrent($site);
        }

        $this->defaults = Seo::find($this->type(), $this->handle())?->in($site);
    }

    public function type(): string
    {
        return match (true) {
            ($this->content instanceof Entry) => 'collections',
            ($this->content instanceof Taxonomy) => 'taxonomies',
            ($this->content instanceof LocalizedTerm) => 'taxonomies',
        };
    }

    public function handle(): string
    {
        return match (true) {
            ($this->content instanceof Entry) => $this->content->collectionHandle(),
            ($this->content instanceof Taxonomy) => $this->content->handle(),
            ($this->content instanceof LocalizedTerm) => $this->content->taxonomyHandle(),
        };
    }

    public function path(): string
    {
        return parse_url($this->loc())['path'] ?? '/';
    }

    public function loc(): string
    {
        $canonicalType = $this->content->value('seo_canonical_type') ?? $this->defaults?->value('seo_canonical_type');

        if ($canonicalType === 'other') {
            $entryId = $this->content->value('seo_canonical_entry') ?? $this->defaults?->value('seo_canonical_entry');

            return EntryFacade::find($entryId)->absoluteUrl();
        }

        if ($canonicalType === 'custom') {
            return $this->content->value('seo_canonical_custom') ?? $this->defaults?->value('seo_canonical_custom');
        }

        return $this->content->absoluteUrl();
    }

    public function lastmod(): string
    {
        return method_exists($this->content, 'lastModified')
            ? $this->content->lastModified()->format('Y-m-d\TH:i:sP')
            : now()->format('Y-m-d\TH:i:sP');
    }

    public function changefreq(): string
    {
        return $this->content->value('seo_sitemap_change_frequency')
            ?? $this->defaults?->value('seo_sitemap_change_frequency')
            ?? ContentDefaultsFields::getDefaultValue('seo_sitemap_change_frequency');
    }

    public function priority(): string
    {
        return $this->content->value('seo_sitemap_priority')
            ?? $this->defaults?->value('seo_sitemap_priority')
            ?? ContentDefaultsFields::getDefaultValue('seo_sitemap_priority');
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path(),
            'loc' => $this->loc(),
            'lastmod' => $this->lastmod(),
            'changefreq' => $this->changefreq(),
            'priority' => $this->priority(),
        ];
    }
}