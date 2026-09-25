<?php namespace CRSCompany\FrameworC\Models;

use CRSCompany\FrameworC\Classes\CustomScss;
use Model;

/**
 * FrameworcSetting Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class FrameworcSetting extends \System\Models\SettingModel
{
    public $settingsCode = 'frameworc_settings';

    public $settingsFields = 'fields.yaml';

    public function beforeSave()
    {
        CustomScss::validate((string) ($this->wrapper['styles_globalScss'] ?? ''), 'wrapper[styles_globalScss]');
    }
} 