<?php namespace CRS\FrameworC\Components;

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
                $this->addComponent("\\CRS\\FrameworC\\Components\\" . $block->content_group, $block->content_group . 'Column' . $c . 'Block' . $i, [
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
