<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * Meta Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Meta extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Meta Component',
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

    public function init() {
        $section = EntryRecord::inSection('Meta')
            ->first();

        $this->page['meta'] = $section;
    }
}
