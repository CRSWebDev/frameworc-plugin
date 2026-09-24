<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Gallery Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Gallery extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Gallery Component',
            'description' => 'Galerie obrázků'
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
