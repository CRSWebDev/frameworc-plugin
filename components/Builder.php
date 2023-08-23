<?php namespace CRS\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Tailor\Models\EntryRecord;

/**
 * Builder Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Builder extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Builder Component',
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

    public function init()
    {
        $slug = $this->param('fullslug');

        $section = EntryRecord::inSection('Builder')
            ->where('fullslug', $slug)
            ->first();

        if (empty($section)) {
            return Redirect::to('/404');
        }

        $this->page['record'] = $section;

        foreach ($section->builder as $i => $block) {
            $this->addComponent("\\CRS\\FrameworC\\Components\\" . $block->content_group, $block->content_group . $i, [
                'block' => $block,
            ]);
        }
    }

    public function onRun()
    {
        $this->addCss(['components/builder/normalize.scss']);
        $this->addCss(['components/builder/base.scss']);
    }
}
