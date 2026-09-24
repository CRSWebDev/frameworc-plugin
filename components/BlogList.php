<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Support\Facades\Input;
use Tailor\Models\EntryRecord;

/**
 * BlogList Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class BlogList extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'BlogList Component',
            'description' => 'Výpis příspěvků blogu s filtrem podle štítků'
        ];
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }

    public function posts($perPage = 5, $uniqueId = null, $hasPagination = true)
    {
        $perPage = max(1, (int) $perPage);
        $currentPage = max(1, (int) Input::get('p', 1));
        $tag = Input::get('t');

        $posts = $this->postsQuery($tag)
            ->orderBy('published_at_date', 'desc');

        if ($hasPagination) {
            $posts->limit($perPage * $currentPage);
        } else {
            $posts->limit($perPage);
        }

        $posts = $posts->get();

        return [
            'uniqueId' => $uniqueId ?: 'default',
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'nextPage' => $currentPage + 1,
            'tag' => $tag,
            'total' => $this->postsQuery($tag)->count(),
            'items' => $posts,
        ];
    }

    public function onLoadMore() {
        $perPage = max(1, (int) Input::get('perPage'));
        $nextPage = max(1, (int) Input::get('nextPage'));
        $uniqueId = Input::get('uniqueId');
        $tag = Input::get('t');

        $posts = $this->postsQuery($tag)
            ->orderBy('published_at_date', 'desc')
            ->skip($perPage * ($nextPage - 1))
            ->take($perPage)
            ->get();

        $returnArray = [];

        $returnArray['@#blog-wrapper-' . $uniqueId] = $this->renderPartial('@post-items', [
            'posts' => $posts,
        ]);

        $returnArray['#blog-load-more-wrapper-' . $uniqueId] = $this->renderPartial('@post-load-more', [
            'posts' => [
                'uniqueId' => $uniqueId,
                'perPage' => $perPage,
                'currentPage' => $nextPage,
                'nextPage' => $nextPage + 1,
                'tag' => $tag,
                'total' => $this->postsQuery($tag)->count(),
                'items' => $posts,
            ]
        ]);

        return $returnArray;
    }

    /**
     * postsQuery returns enabled blog posts, optionally limited to a tag slug
     */
    protected function postsQuery($tag = null)
    {
        $query = EntryRecord::inSection('BlogPost')->where('is_enabled', 1);

        if ($tag) {
            $query->whereHas('tags', function ($query) use ($tag) {
                $query->where('slug', $tag);
            });
        }

        return $query;
    }

    public function getTags() {
        return EntryRecord::inSection('Tags')->get();
    }
}
