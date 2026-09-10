<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use CRSCompany\FrameworC\Classes\SettingsHelper;
use CRSCompany\FrameworC\Models\FrameworcSetting;
use Event;
use Redirect;
use Tailor\Models\EntryRecord;

/**
 * Builder Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Builder extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Builder Component',
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

    /**
     * @var \Tailor\Models\EntryRecord record resolved for the current URL
     */
    protected $record;

    /**
     * @var array sitePickerSlugCache of resolved slugs, keyed by site id
     */
    protected $sitePickerSlugCache = [];

    public function init()
    {
        $slug = $this->param('fullslug');

        // The route pattern (/:fullslug?*) leaves the parameter empty at the site
        // root, which is where the "home" entry lives. The router reports an
        // unfilled optional segment as false rather than null.
        if (empty($slug)) {
            $slug = 'home';
        }

        $section = EntryRecord::inSection('Builder')
            ->where('is_enabled', 1)
            ->where('fullslug', $slug)
            ->first();

        if (empty($section)) {
            $this->controller->setStatusCode(404);
            return;
        }

        $this->record = $section;
        $this->page['record'] = $section;

        // This theme uses its own component instead of Tailor's `section`, so the
        // listener that translates site picker URLs across sites is never
        // registered. Do it here, see Tailor\Components\SectionComponent::init().
        Event::listen('cms.sitePicker.overridePattern', function ($page, $pattern, $currentSite, $proposedSite) {
            // At the site root there is no slug segment at all. The router cannot
            // collapse an empty optional segment on its own (it emits its own
            // fallback value instead), so hand it a pattern with nothing to fill.
            if ($this->getSitePickerSlug($proposedSite) === null) {
                return '/';
            }
        });

        Event::listen('cms.sitePicker.overrideParams', function ($page, $params, $currentSite, $proposedSite) {
            $fullslug = $this->getSitePickerSlug($proposedSite);

            if ($fullslug === null) {
                return;
            }

            return array_merge($params, ['fullslug' => $fullslug]);
        });

        if (!empty($section) && !empty($section->builder)) {
            foreach ($section->builder as $i => $block) {
                $alias = !empty($block->aliasOverride) ? $block->aliasOverride : $block->content_group . $i;

                $this->addComponent("\\CRSCompany\\FrameworC\\Components\\" . $block->content_group, $alias, [
                    'block' => $block,
                    'block_identifier' => $block->content_group . $i,
                ]);
            }
        }
    }

    /**
     * getSitePickerSlug returns the slug of the current record's counterpart on the
     * given site, or null when that page lives at the site root. Both site picker
     * listeners need the answer for the same site, so it is resolved once.
     */
    protected function getSitePickerSlug($site)
    {
        if (array_key_exists($site->id, $this->sitePickerSlugCache)) {
            return $this->sitePickerSlugCache[$site->id];
        }

        $fullslug = null;

        if (!empty($this->record)) {
            $otherRecord = $this->record
                ->newOtherSiteQuery()
                ->where('site_id', $site->id)
                ->where('is_enabled', 1)
                ->first();

            $fullslug = $otherRecord->fullslug ?? null;
        }

        // The home entry lives at the site root and has no slug in the URL. The
        // same fallback applies when the page has no counterpart on that site.
        if ($fullslug === 'home') {
            $fullslug = null;
        }

        return $this->sitePickerSlugCache[$site->id] = $fullslug;
    }

    public function onRun()
    {
        if (request()->is('home')) {
            return Redirect::to('/');
        }

        $this->addCss('components/slider/css/splide.min.css');
        $this->addCss(['components/builder/normalize.scss']);
        $this->addCss(['components/builder/base.scss']);

        $this->addJs('components/form/altcha.js', [
            'async' => true,
            'defer' => true,
            'type' => 'module'
        ]);

        $this->addJs([
            'components/builder/global.js',
            'components/accordion/Accordion.js',
            'components/form/Form.js',
            'components/imagestrip/ImageStrip.js',
            'components/slider/js/splide.min.js',
            'components/slider/Slider.js',
            'components/tabs/Tabs.js',
            'components/navigation/Navigation.js',
            'components/menublock/MenuBlock.js',
            'components/gallery/Gallery.js',
        ]);

        $this->page['fwcSettings'] = SettingsHelper::getAll();
    }
}
