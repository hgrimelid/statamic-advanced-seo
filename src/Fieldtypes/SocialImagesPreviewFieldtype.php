<?php

namespace Aerni\AdvancedSeo\Fieldtypes;

use Aerni\AdvancedSeo\Facades\SocialImage;
use Statamic\Fields\Fieldtype;

class SocialImagesPreviewFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload(): array
    {
        $parent = $this->field->parent();
        $type = $this->config()['image_type'];
        $specs = SocialImage::specs($type, $parent);

        return SocialImage::make($parent, $specs)->toFieldtypeArray();
    }
}
