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

    public function posts($perPage = 5, $uniqueId = null, $hasPagination = true)
    {
        $currentPage = Input::get('p', 1);

        $posts = EntryRecord::inSection('BlogPost')
            ->orderBy('published_at_date', 'desc');

        if ($hasPagination) {
            $posts->limit($perPage * $currentPage);
        } else {
            $posts->limit($perPage);
        }

        if (Input::get('t')) {
            $posts->whereHas('tags', function ($query) {
                $query->where('slug', Input::get('t'));
            });
        }

        $posts = $posts->get();

        return [
            'uniqueId' => $uniqueId ?: 'default',
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'nextPage' => $currentPage + 1,
            'tag' => Input::get('t'),
            'total' => EntryRecord::inSection('BlogPost')->count(),
            'items' => $posts,
        ];
    }

    public function onLoadMore() {
        $perPage = Input::get('perPage');
        $nextPage = Input::get('nextPage');
        $uniqueId = Input::get('uniqueId');

        $posts = EntryRecord::inSection('BlogPost')
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
                'tag' => Input::get('t'),
                'total' => EntryRecord::inSection('BlogPost')->count(),
                'items' => $posts,
            ]
        ]);

        return $returnArray;
    }

    public function getTags() {
        return EntryRecord::inSection('Tags')->get();
    }
}
