<?php namespace CRS\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * Footer Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Footer extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Footer Component',
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
        $this->addCss(['components/footer/Footer.scss']);
    }

    public function getNav() {
        $nav = EntryRecord::inSection('Footer')
            ->first();

        return $nav;
    }
}
