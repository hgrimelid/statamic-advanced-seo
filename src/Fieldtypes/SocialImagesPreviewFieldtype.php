<?php

namespace Aerni\AdvancedSeo\Fieldtypes;

use Aerni\AdvancedSeo\Facades\SocialImage;
use Statamic\Fields\Fieldtype;

class SocialImagesPreviewFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload(): array
    {
        /**
         * TODO: When creating an entry, the parent is a collection and not an Entry.
         * So it fill throw an error. Fix it.
         */
        $parent = $this->field->parent();
        $type = $this->config()['image_type'];
        $specs = SocialImage::specs($type, $parent);

        return SocialImage::make($parent, $specs)->toFieldtypeArray();
    }
}
