<?php namespace CRSCompany\FrameworC\Components;

use BackendAuth;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Storage;
use October\Rain\Support\Facades\Input;
use Tailor\Models\EntryRecord;

/**
 * Meta Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Meta extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Meta Component',
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
        $section = EntryRecord::inSection('Meta')
            ->first();

        $this->page['meta'] = $section;
    }

    public function onFaviconGenerated() {
        $result = json_decode(Input::get('json_result'))->favicon_generation_result;

        if ($result->custom_parameter != 'ref=' . env('RFG_REF_PARAM')) {
            throw new \Exception('Invalid favicon generation result');
        }

        $loggedIn = BackendAuth::check();
        if (empty($loggedIn)) {
            throw new \Exception('Not logged in');
        }

        $file = file_get_contents($result->favicon->package_url);
        Storage::put('media/favicon/package.zip', $file);
        // Unzip the file
        $zip = new \ZipArchive;
        $zip->open(storage_path('app/media/favicon/package.zip'));
        $zip->extractTo(storage_path('app/media/favicon'));
        $zip->close();

        return '/admin/tailor/entries/meta?html_code=' . urlencode($result->favicon->html_code);
    }
}
