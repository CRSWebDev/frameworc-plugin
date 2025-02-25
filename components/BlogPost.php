<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * BlogPost Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class BlogPost extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'BlogPost Component',
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

    public function onRun()
    {
        $this->addComponent('CRSCompany\FrameworC\Components\Downloads', 'Downloads', []);
        $this->addComponent('CRSCompany\FrameworC\Components\Gallery', 'Gallery', []);
    }

    public function post()
    {
        $slug = $this->getPostSlug($this->param('fullslug'));
        $post = EntryRecord::inSection('BlogPost')
            ->where('is_enabled', 1)
            ->where('slug', $slug)
            ->first();

        $this->page['post'] = $post;

        return $post;
    }

    private function getPostSlug($slug)
    {
        $slug = explode('/', $slug);
        return end($slug);
    }
}
