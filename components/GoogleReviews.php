<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use CRSCompany\FrameworC\Classes\GoogleReviews as GoogleReviewsSync;
use Tailor\Models\EntryRecord;

/**
 * GoogleReviews Component
 *
 * Renders reviews stored on a GooglePlace entry. Downloading them is done by
 * the frameworc:google-reviews command, never during a page request.
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class GoogleReviews extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'GoogleReviews Component',
            'description' => 'Recenze firmy z Googlu'
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
     * getPlace returns the block's selected place, or the first one when none is selected.
     */
    public function getPlace($block)
    {
        if ($block && $block->place) {
            return $block->place;
        }

        return EntryRecord::inSection(GoogleReviewsSync::SECTION)->first();
    }

    /**
     * getReviews returns the visible reviews of a place, newest first.
     */
    public function getReviews($place, $data)
    {
        if (!$place) {
            return [];
        }

        $minRating = (int) ($data->minRating ?? 4);
        $count = (int) ($data->reviewCount ?? 6) ?: 6;

        return $place->reviews
            ->filter(fn ($review) => !$review->hidden && (int) $review->rating >= $minRating)
            ->sortByDesc(fn ($review) => (string) $review->publishTime)
            ->take($count)
            ->values();
    }
}
