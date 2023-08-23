<?php namespace CRS\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Slider Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Slider extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Slider Component',
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

    public function onRun()
    {
        $this->addCss(['components/slider/Slider.scss']);
        $this->addJs(['components/slider/Slider.js']);
    }
}
