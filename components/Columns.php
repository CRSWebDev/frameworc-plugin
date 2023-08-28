<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;

/**
 * Columns Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Columns extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Columns Component',
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
        $columns = $this->properties['block']->content->columns;

        foreach ($columns as $c => $column) {
            foreach ($column->builder as $i => $block) {
                $alias = !empty($block->aliasOverride) ? $block->aliasOverride : $block->content_group . 'Column' . $c . 'Block' . $i;

                $this->addComponent("\\CRSCompany\\FrameworC\\Components\\" . $block->content_group, $alias, [
                    'block' => $block,
                ]);
            }
        }
    }

    public function onRun()
    {
        $this->addCss(['components/columns/Columns.scss']);
    }
}
