<?php namespace CRSCompany\FrameworC\Models;

use CRSCompany\FrameworC\Classes\CustomScss;

/**
 * FrameworcSiteSetting Model, holds settings that differ per site
 *
 * @link https://docs.octobercms.com/3.x/extend/settings/model-settings.html
 */
class FrameworcSiteSetting extends \System\Models\SettingModel
{
    use \October\Rain\Database\Traits\Multisite;

    public $settingsCode = 'frameworc_site_settings';

    public $settingsFields = 'fields.yaml';

    protected $propagatable = [];

    public function beforeSave()
    {
        CustomScss::validate(CustomScss::source(null, (string) $this->siteScss), 'siteScss');
    }
}
