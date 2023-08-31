<?php namespace CRSCompany\FrameworC;

use Backend;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Storage;
use Media\Classes\MediaLibrary;
use Media\Widgets\MediaManager;
use October\Rain\Support\Facades\Event;
use October\Rain\Support\Facades\File;
use System\Classes\PluginBase;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'FrameworC',
            'description' => 'No description provided yet...',
            'author' => 'CRS',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        Event::listen('cms.pageLookup.getTypeInfo', function($type) {
            if ($type == 'entry-b340635e-2eea-44de-82a2-ea08c5d48d31') {
                return [
                    'cmsPages' => Page::withComponent('Builder')->all()
                ];
            }
        });
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'CRSCompany\FrameworC\Components\Navigation' => 'Navigation',
            'CRSCompany\FrameworC\Components\Footer' => 'Footer',
            'CRSCompany\FrameworC\Components\Builder' => 'Builder',
            'CRSCompany\FrameworC\Components\Header' => 'Header',
            'CRSCompany\FrameworC\Components\Section' => 'Section',
            'CRSCompany\FrameworC\Components\Tiles' => 'Tiles',
            'CRSCompany\FrameworC\Components\Slider' => 'Slider',
            'CRSCompany\FrameworC\Components\Accordion' => 'Accordion',
            'CRSCompany\FrameworC\Components\Form' => 'Form',
            'CRSCompany\FrameworC\Components\Columns' => 'Columns',
            'CRSCompany\FrameworC\Components\Prefill' => 'Prefill',
            'CRSCompany\FrameworC\Components\Meta' => 'Meta',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'crs.frameworc.some_permission' => [
                'tab' => 'FrameworC',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'frameworc' => [
                'label' => 'FrameworC',
                'url' => Backend::url('crs/frameworc/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['crs.frameworc.*'],
                'order' => 500,
            ],
        ];
    }

    public function registerMarkupTags() {
        return [
            'filters' => [
                'moduleMods' => [$this, 'moduleModsFilter'],
                'svg' => [$this, 'svgFilter'],
                'json_decode' => [$this, 'jsonDecodeFilter'],
                'base64_encode' => [$this, 'getBase64'],
            ],
        ];
    }

    public function moduleModsFilter($data, $module, array $props = []) {
        $cls = [];
        foreach ($props as $prop) {
            if (!empty($data[$prop])) {
                $cls[] = $module . '--' . $prop;
            }
        }

        return implode(' ', $cls);
    }

    public function svgFilter($url) {
        $svg = Storage::disk('media')->get($url);
        return $svg;
    }

    public function jsonDecodeFilter($json) {
        return json_decode($json, true);
    }

    public function getBase64($url) {
        $file = file_get_contents($url);
        $base64 = base64_encode($file);
        return $base64;
    }
}
