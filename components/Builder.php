<?php namespace CRSCompany\FrameworC\Components;

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
            $alias = !empty($block->aliasOverride) ? $block->aliasOverride : $block->content_group . $i;

            $this->addComponent("\\CRSCompany\\FrameworC\\Components\\" . $block->content_group, $alias, [
                'block' => $block,
            ]);
        }
    }

    public function onRun()
    {
        $this->addCss(['components/builder/normalize.scss']);
        $this->addCss(['components/builder/base.scss']);

        $this->addJs([
            'components/accordion/Accordion.js',
            'components/form/Form.js',
            'components/imagestrip/ImageStrip.js',
            'components/slider/Slider.js',
        ]);
    }
}
