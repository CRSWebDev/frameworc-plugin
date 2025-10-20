<?php namespace CRSCompany\FrameworC\Components;

use BackendAuth;
use Cms\Classes\ComponentBase;
use CRSCompany\FrameworC\Classes\SettingsHelper;
use CRSCompany\FrameworC\Models\FrameworcSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use October\Rain\Filesystem\Filesystem;
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
        $cssVariables = $this->getCssVariables();
        $section = EntryRecord::inSection('Meta')
            ->first();

        $this->page['meta'] = $section;
        $this->page['cssVars'] = $cssVariables;
    }

    public function onFaviconGenerated() {
        $faviconUrl = Input::get('json_result_url');

        $resp = Http::get('https://realfavicongenerator.net' . $faviconUrl);

        $result = json_decode($resp->body())->favicon_generation_result;

        if ($result->custom_parameter != 'ref=as2d584jz8d25sg8s3af8h') {
            throw new \Exception('Invalid favicon generation result');
        }

        $loggedIn = BackendAuth::check();
        if (empty($loggedIn)) {
            throw new \Exception('Not logged in');
        }

        $dir = new Filesystem;
        $dir->cleanDirectory(storage_path('app/media/favicon'));

        $file = file_get_contents($result->favicon->package_url);
        Storage::put('media/favicon/package.zip', $file);
        // Unzip the file
        $zip = new \ZipArchive;
        $zip->open(storage_path('app/media/favicon/package.zip'));
        $zip->extractTo(storage_path('app/media/favicon'));
        $zip->close();

        return '/admin/tailor/entries/meta?html_code=' . urlencode($result->favicon->html_code);
    }

    private function getCssVariables()
    {
        $settings = FrameworcSetting::instance();

        $settingCss = SettingsHelper::getByPrefix('variable_', $settings);

        $css = $settingCss['variablesScss'] ?? '';

        return $css;
    }
}
