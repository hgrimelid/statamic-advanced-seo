<?php

namespace Aerni\AdvancedSeo\Sitemap;

use Aerni\AdvancedSeo\Models\Defaults;
use Illuminate\Support\Carbon;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class CustomSitemapUrl extends BaseSitemapUrl
{
    use FluentlyGetsAndSets;

    public function __construct(protected string $loc)
    {
    }

    public function loc(string $loc = null): string|self
    {
        return $this->fluentlyGetOrSet('loc')->args(func_get_args());
    }

    public function alternates(array $alternates = null): array|self|null
    {
        return $this->fluentlyGetOrSet('alternates')
            ->setter(function ($alternates) {
                foreach ($alternates as $alternate) {
                    throw_unless(array_key_exists('href', $alternate), new \Exception("One of your alternate links is missing the 'href' attribute."));
                    throw_unless(array_key_exists('hreflang', $alternate), new \Exception("One of your alternate links is missing the 'hreflang' attribute."));
                }

                return $alternates;
            })
            ->args(func_get_args());
    }

    public function lastmod(Carbon $lastmod = null): string|self|null
    {
        return $this->fluentlyGetOrSet('lastmod')
            ->getter(function () {
                return $this->lastmod ?? now()->format('Y-m-d\TH:i:sP');
            })
            ->setter(function ($lastmod) {
                return $lastmod->format('Y-m-d\TH:i:sP');
            })
            ->args(func_get_args());
    }

    public function changefreq(string $changefreq = null): string|self|null
    {
        return $this->fluentlyGetOrSet('changefreq')
            ->getter(function () {
                return $this->changefreq ?? Defaults::data('collections')->get('seo_sitemap_change_frequency');
            })
            ->setter(function ($changefreq) {
                $allowedValues = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
                $allowedValuesString = implode(', ', $allowedValues);

                throw_unless(in_array($changefreq, $allowedValues), new \Exception("Make sure to use a valid 'changefreq' value. Valid values are: [$allowedValuesString]."));

                return $changefreq;
            })
            ->args(func_get_args());
    }

    public function priority(string $priority = null): string|self|null
    {
        return $this->fluentlyGetOrSet('priority')
            ->getter(function () {
                return $this->priority ?? Defaults::data('collections')->get('seo_sitemap_priority');
            })
            ->setter(function ($priority) {
                $allowedValues = ['0.0', '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0'];
                $allowedValuesString = implode(', ', $allowedValues);

                throw_unless(in_array($priority, $allowedValues), new \Exception("Make sure to use a valid 'priority' value. Valid values are: [$allowedValuesString]."));

                return $priority;
            })
            ->args(func_get_args());
    }
}