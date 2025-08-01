<?php namespace CRSCompany\FrameworC\Classes;

use CRSCompany\FrameworC\Models\FrameworcSetting;

class SettingsHelper {
    public static function getByPrefix($prefix)
    {
        $settings = FrameworcSetting::instance();
        $result = [];
        $settingsArray = $settings->toArray();
        
        foreach ($settingsArray['wrapper'] as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                // Remove prefix and convert to camelCase for array keys
                $cleanKey = str_replace($prefix, '', $key);
                $result[$cleanKey] = $value;
            }
        }

        return $result;
    }
}