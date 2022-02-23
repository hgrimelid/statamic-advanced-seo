<?php

namespace Aerni\AdvancedSeo\Http\Controllers\Cp;

use Edalzell\Forma\ConfigController as BaseController;
use Illuminate\Support\Arr;

class ConfigController extends BaseController
{
    protected function postProcess(array $values): array
    {
        $disabledCollections = Arr::pull($values, 'disabled_collections') ?? [];
        $disabledTaxonomies = Arr::pull($values, 'disabled_taxonomies') ?? [];

        return [
            'disabled' => [
                'collections' => $disabledCollections,
                'taxonomies' => $disabledTaxonomies,
            ],
        ];
    }

    protected function preProcess(string $handle): array
    {
        $config = config($handle);

        return [
            'disabled_collections' => $config['disabled']['collections'],
            'disabled_taxonomies' => $config['disabled']['taxonomies'],
        ];
    }
}
