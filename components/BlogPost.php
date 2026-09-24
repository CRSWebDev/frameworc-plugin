<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use System\Classes\PluginManager;
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
            'description' => 'Detail příspěvku blogu'
        ];
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * @var \Tailor\Models\EntryRecord|null|false post resolved for the current URL, false until looked up
     */
    protected $post = false;

    public function onRun()
    {
        $this->addComponent('CRSCompany\FrameworC\Components\Downloads', 'Downloads', []);
        $this->addComponent('CRSCompany\FrameworC\Components\Gallery', 'Gallery', []);

        $plugin = PluginManager::instance()->findByIdentifier('CRSCompany.FrameworC');

        if (!$plugin->isBlogPost(Meta::getMeta())) {
            return;
        }

        // Builder is attached to the same page and flags every URL it does not
        // own as 404 in its init(), so a found post resets the status here. This
        // runs in onRun because the layout renders Meta before the page body.
        $post = $this->post();

        if ($post) {
            $this->controller->setStatusCode(200);
            $this->page['record'] = $post;
        }
    }

    public function post()
    {
        if ($this->post !== false) {
            return $this->post;
        }

        $slug = $this->getPostSlug($this->param('fullslug'));
        $post = EntryRecord::inSection('BlogPost')
            ->where('is_enabled', 1)
            ->where('slug', $slug)
            ->first();

        $this->page['post'] = $post;

        return $this->post = $post;
    }

    private function getPostSlug($slug)
    {
        $slug = explode('/', $slug);
        return end($slug);
    }
}
