<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * ImageStrip Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class ImageStrip extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'ImageStrip Component',
            'description' => 'Pás obrázků (např. log partnerů) s automatickým posunem'
        ];
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }
}
