<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Prefill Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Prefill extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Prefill Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function init() {
        $id = $this->properties['block']->content->id;
        $blocks = $this->properties['block']->content->block->builder;

        foreach ($blocks as $b => $block) {
            $alias = !empty($block->aliasOverride) ? $block->aliasOverride : $block->content_group . 'Prefill' . $id . 'Block' . $b;

            $this->addComponent("\\CRSCompany\\FrameworC\\Components\\" . $block->content_group, $alias, [
                'block' => $block,
            ]);
        }
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }
}
