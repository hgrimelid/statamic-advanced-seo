<?php

namespace Aerni\AdvancedSeo\Sitemap;

use Statamic\Facades\Site;
use Illuminate\Support\Collection;
use Aerni\AdvancedSeo\Support\Helpers;
use Statamic\Contracts\Taxonomies\Term;

class TermSitemapUrl extends BaseSitemapUrl
{
    public function __construct(protected Term $term, protected TaxonomySitemap $sitemap)
    {
    }

    public function loc(): string
    {
        return $this->term->absoluteUrl();
    }

    public function alternates(): array
    {
        $terms = $this->terms();

        // We only want alternate URLs if there are at least two terms.
        if ($terms->count() <= 1) {
            return [];
        }

        return $terms->map(fn ($term) => [
            'hreflang' => Helpers::parseLocale(Site::get($term->locale())->locale()),
            'href' => $term->absoluteUrl(),
        ])->toArray();
    }

    public function lastmod(): string
    {
        return $this->term->lastModified()->format('Y-m-d\TH:i:sP');
    }

    public function changefreq(): string
    {
        return $this->term->seo_sitemap_change_frequency;
    }

    public function priority(): string
    {
        // Make sure we actually return `0.0` and `1.0`.
        return number_format($this->term->seo_sitemap_priority->value(), 1);
    }

    public function isCanonicalUrl(): bool
    {
        return match ($this->term->seo_canonical_type->value()) {
            'current' => true,
            default => false,
        };
    }

    protected function terms(): Collection
    {
        return $this->sitemap->terms($this->term->taxonomy())
            ->filter(fn ($term) => $term->id() === $this->term->id());
    }
}
