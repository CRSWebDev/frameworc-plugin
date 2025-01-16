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
            ->where('is_enabled', 1)
            ->where('fullslug', $slug)
            ->first();

        $this->page['record'] = $section;

        if (!empty($section) && !empty($section->builder)) {
            foreach ($section->builder as $i => $block) {
                $alias = !empty($block->aliasOverride) ? $block->aliasOverride : $block->content_group . $i;

                $this->addComponent("\\CRSCompany\\FrameworC\\Components\\" . $block->content_group, $alias, [
                    'block' => $block,
                    'block_identifier' => $block->content_group . $i,
                ]);
            }
        }
    }

    public function onRun()
    {
        $this->addCss(['components/builder/normalize.scss']);
        $this->addCss(['components/builder/base.scss']);

        $this->addJs('components/form/altcha.js', [
            'async' => true,
            'defer' => true,
            'type' => 'module'
        ]);

        $this->addJs([
            'components/builder/global.js',
            'components/accordion/Accordion.js',
            'components/form/Form.js',
            'components/imagestrip/ImageStrip.js',
            'components/slider/Slider.js',
            'components/navigation/Navigation.js',
            'components/menublock/MenuBlock.js',
            'components/gallery/Gallery.js',
        ]);

    }
}
