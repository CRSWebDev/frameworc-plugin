<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Cache;
use Yizack\InstagramFeed;

/**
 * InstaFeed Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class InstaFeed extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'InstaFeed Component',
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

    public function getInstaFeed($token) {
        $igFeed = new InstagramFeed($token);

        try {
            $feed = Cache::remember('instafeed_' . md5($token), 60 * 15, function() use ($igFeed) {
                return $igFeed->getFeed(['media_url','permalink','thumbnail_url','media_type']);
            });

            return $feed;
        } catch (\Exception $e) {
            return [];
        }
    }
}
