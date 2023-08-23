<?php namespace CRS\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Tiles Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Tiles extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Tiles Component',
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
        $this->addCss(['components/tiles/Tiles.scss']);
    }
}
