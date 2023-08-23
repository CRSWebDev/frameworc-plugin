<?php namespace CRS\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Header Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Header extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Header Component',
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
        $this->addCss(['components/header/Header.scss']);
    }
}
