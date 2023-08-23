<?php namespace CRS\FrameworC;

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
            'CRS\FrameworC\Components\Navigation' => 'Navigation',
            'CRS\FrameworC\Components\Footer' => 'Footer',
            'CRS\FrameworC\Components\Builder' => 'Builder',
            'CRS\FrameworC\Components\Header' => 'Header',
            'CRS\FrameworC\Components\Section' => 'Section',
            'CRS\FrameworC\Components\Tiles' => 'Tiles',
            'CRS\FrameworC\Components\Slider' => 'Slider',
            'CRS\FrameworC\Components\Accordion' => 'Accordion',
            'CRS\FrameworC\Components\Form' => 'Form',
            'CRS\FrameworC\Components\Columns' => 'Columns',
            'CRS\FrameworC\Components\Prefill' => 'Prefill',
            'CRS\FrameworC\Components\Meta' => 'Meta',
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
}
