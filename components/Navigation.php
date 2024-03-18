<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * Navigation Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Navigation extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Navigation Component',
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

    public function getNav() {
        $nav = EntryRecord::inSection('Navigation')
            ->first();

        return $nav;
    }

    public function getMenu($nav) {
        $menu = [];
        foreach ($nav as $item) {
            if ($item['parent_id'] == null) {
                $menu[$item->id] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank,
                    'children' => []
                ];
            } else {
                $menu[$item->parent_id]['children'][] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank
                ];
            }
        }

        return $menu;
    }
}
