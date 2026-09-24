<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Accordion Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Accordion extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Accordion Component',
            'description' => 'Blok s rozbalovacími položkami'
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
