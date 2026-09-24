<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Tabs Component
 *
 * @link https://docs.octobercms.com/4.x/extend/cms-components.html
 */
class Tabs extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Tabs Component',
            'description' => 'Záložky s obsahem'
        ];
    }

    /**
     * @link https://docs.octobercms.com/4.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }
}
