<?php namespace CRS\Frameworc\Components;

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
        $this->addCss(['components/accordion/Accordion.scss']);
        $this->addJs(['components/accordion/Accordion.js']);
    }
}
