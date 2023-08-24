<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Section Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Section extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Section Component',
            'description' => 'No description provided yet...'
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
