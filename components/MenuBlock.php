<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * MenuBlock Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class MenuBlock extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'MenuBlock Component',
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
        $block = $this->properties['block']->content->menu;

        $menu = EntryRecord::inSection('Menu')
            ->where('id', $block->id)
            ->first();

        $menuItems = [];
        foreach ($menu->navigation as $item) {
            if ($item['parent_id'] == null) {
                $menuItems[$item->id] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank,
                    'children' => []
                ];
            } else {
                $menuItems[$item->parent_id]['children'][] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank
                ];
            }
        }

        $this->properties['menu'] = $menuItems;
    }
}
