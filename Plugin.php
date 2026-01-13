<?php namespace CRSCompany\FrameworC;

use Backend;
use Cms\Classes\Page;
use CRSCompany\FrameworC\Classes\Spacer;
use Illuminate\Support\Facades\Storage;
use Media\Classes\MediaLibrary;
use Media\Widgets\MediaManager;
use October\Rain\Support\Facades\Event;
use October\Rain\Support\Facades\File;
use System\Classes\PluginBase;
use System\Classes\ResizeImages;
use System\Models\SiteDefinition;

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
            'CRSCompany\FrameworC\Components\ImageStrip' => 'ImageStrip',
            'CRSCompany\FrameworC\Components\BlogPost' => 'BlogPost',
            'CRSCompany\FrameworC\Components\BlogList' => 'BlogList',
            'CRSCompany\FrameworC\Components\MenuBlock' => 'MenuBlock',
            'CRSCompany\FrameworC\Components\Gallery' => 'Gallery',
            'CRSCompany\FrameworC\Components\Downloads' => 'Downloads',
            'CRSCompany\FrameworC\Components\Tabs' => 'Tabs',
            'CRSCompany\FrameworC\Components\InstaFeed' => 'InstaFeed',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'FrameworC Settings',
                'description' => 'Obecná nastavení pro FrameworC plugin',
                'category' => 'FrameworC',
                'icon' => 'icon-cog',
                'class' => \CRSCompany\FrameworC\Models\FrameworcSetting::class,
            ]
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
                'svgAsset' => [$this, 'svgAssetFilter'],
                'json_decode' => [$this, 'jsonDecodeFilter'],
                'base64_encode' => [$this, 'getBase64'],
                'spacer' => [$this, 'spacerFilter'],
                'srcset' => [$this, 'srcsetFilter', false],
                'imageset' => [$this, 'imagesetFilter', false],
            ],
            'functions' => [
                'isBlogPost' => [$this, 'isBlogPost'],
                'getBlogPath' => [$this, 'getBlogPath'],
            ]
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
        if (str_contains('.svg', $url)) {
            $svg = Storage::disk('media')->get($url);
            return $svg;
        }

        // Return <img> with jpg, png as is with correct path
        $mediaUrl = MediaLibrary::url($url);
        return '<img src="' . $mediaUrl . '" alt="" />';
    }

    public function svgAssetFilter($url) {
        $url = $url . '.svg';

        return File::get(base_path($url));
    }

    public function jsonDecodeFilter($json) {
        return json_decode($json, true);
    }

    public function getBase64($url) {
        $file = file_get_contents($url);
        $base64 = base64_encode($file);
        return $base64;
    }

    public function isBlogPost($meta) {
        $url = request()->path();

        if (str_contains($url, $meta->blogBasePath . '/')) {
            return true;
        }

        return false;
    }

    public function getBlogPath($meta, $slug = '') {
        $path = '/';

        if (!empty($meta->blogBasePath)) {
            $path .= $meta->blogBasePath . '/';
        }

        $path .= $slug;

        return $path;
    }

    /*
     * Spacer filter adds &nbsp; to content based on Czech language rules.
     */
    public function spacerFilter($content) {
        $content = Spacer::addNbsp($content);
        return $content;
    }

    public function srcsetFilter($image, $initialWidth = 600, $initialHeight = 400, $mode = 'crop') {
        $sizes = [1.5, 2, 3];

        $path = MediaLibrary::url($image);
        $srcset = '';
        foreach ($sizes as $size) {
            $srcset .= ResizeImages::resize($path, intval($initialWidth * $size), intval($initialHeight * $size), ['mode' => $mode]) . ' ' . $size . 'x';
            if ($size !== end($sizes)) {
                $srcset .= ', ';
            }
        }

        return $srcset;
    }

    public function imagesetFilter($image, $initialWidth = 600, $initialHeight = 400, $mode = 'crop') {
        $sizes = [1, 1.5, 2, 3];

        $path = MediaLibrary::url($image);
        $srcset = 'image-set(';
        foreach ($sizes as $size) {
            $srcset .= 'url(\'' . ResizeImages::resize($path, intval($initialWidth * $size), intval($initialHeight * $size), ['mode' => $mode]) . '\') ' . $size . 'x';
            if ($size !== end($sizes)) {
                $srcset .= ', ';
            }
        }

        $srcset .= ')';

        return $srcset;
    }
}
