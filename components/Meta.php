<?php namespace CRSCompany\FrameworC\Components;

use Backend;
use BackendAuth;
use Cms\Classes\ComponentBase;
use CRSCompany\FrameworC\Classes\SettingsHelper;
use CRSCompany\FrameworC\Models\FrameworcSetting;
use Illuminate\Support\Facades\Http;
use File;
use Flash;
use October\Rain\Support\Facades\Input;
use Site;
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

    static function getMeta() {
        $meta = EntryRecord::inSection('Meta')
            ->first();

        return $meta;
    }

    /**
     * onFaviconGenerated handles the RealFaviconGenerator callback (theme page
     * /api/rfg): downloads the generated package into media/favicon and saves
     * its HTML markup to the Meta entry of the site that started the request.
     * Returns the backend URL to redirect to.
     */
    public function onFaviconGenerated()
    {
        $backendUrl = Backend::url('tailor/entries/meta');

        if (!BackendAuth::check()) {
            return $backendUrl;
        }

        try {
            $this->installFaviconPackage(Input::get('json_result_url'));
            Flash::success('Favikona byla vygenerována a uložena.');
        } catch (\Exception $e) {
            Flash::error('Generování favikony selhalo: ' . $e->getMessage());
        }

        return $backendUrl;
    }

    private function installFaviconPackage($resultPath)
    {
        // The request asks for short_url + path_only, so only a path arrives here.
        if (!is_string($resultPath) || !str_starts_with($resultPath, '/')) {
            throw new \Exception('Chybí odkaz na výsledek generování.');
        }

        $response = Http::get('https://realfavicongenerator.net' . $resultPath);
        $result = $response->ok() ? ($response->json('favicon_generation_result') ?? []) : [];

        if (($result['result']['status'] ?? null) !== 'success') {
            throw new \Exception('RealFaviconGenerator nevrátil úspěšný výsledek.');
        }

        parse_str((string) ($result['custom_parameter'] ?? ''), $custom);

        if (($custom['ref'] ?? null) !== 'as2d584jz8d25sg8s3af8h') {
            throw new \Exception('Neplatná odpověď generátoru.');
        }

        $packageUrl = $result['favicon']['package_url'] ?? null;
        $htmlCode = $result['favicon']['html_code'] ?? null;

        if (!$packageUrl || !$htmlCode) {
            throw new \Exception('Odpověď neobsahuje balíček ikon.');
        }

        $package = Http::get($packageUrl);

        if (!$package->ok()) {
            throw new \Exception('Balíček ikon se nepodařilo stáhnout.');
        }

        $zipPath = temp_path('favicon-' . uniqid() . '.zip');
        File::put($zipPath, $package->body());

        try {
            $zip = new \ZipArchive;

            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Balíček ikon není platný ZIP.');
            }

            // Old icons are only removed once the new package is known to be good.
            $faviconDir = storage_path('app/media/favicon');
            File::makeDirectory($faviconDir, 0755, true, true);
            File::cleanDirectory($faviconDir);

            $zip->extractTo($faviconDir);
            $zip->close();
        } finally {
            File::delete($zipPath);
        }

        Site::withContext((int) ($custom['site'] ?? 0) ?: null, function () use ($htmlCode) {
            $meta = EntryRecord::inSection('Meta')->first();

            if (!$meta) {
                throw new \Exception('Záznam Meta pro tento web neexistuje.');
            }

            $meta->faviconHtml = $htmlCode;
            $meta->save();
        });
    }

    private function getCssVariables()
    {
        $settings = FrameworcSetting::instance();

        $settingCss = SettingsHelper::getByPrefix('variable_', $settings);

        $css = $settingCss['variablesScss'] ?? '';

        return $css;
    }
}
