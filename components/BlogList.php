<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * BlogList Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class BlogList extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'BlogList Component',
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

    public function posts($perPage = 5)
    {
        return EntryRecord::inSection('BlogPost')->paginate($perPage);
    }
}
