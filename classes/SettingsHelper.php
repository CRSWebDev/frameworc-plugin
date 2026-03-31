<?php namespace CRSCompany\FrameworC\Classes;

use CRSCompany\FrameworC\Models\FrameworcSetting;

class SettingsHelper {
    public static function getByPrefix($prefix): array
    {
        $settings = FrameworcSetting::instance();
        $result = [];
        $settingsArray = $settings->toArray();

        if (!isset($settingsArray['wrapper'])) {
            return $result;
        }

        foreach ($settingsArray['wrapper'] as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                // Check if last character of prefix is underscore
                if (substr($prefix, -1) !== '_') {
                    $prefix .= '_';
                }

                // Remove prefix and convert to camelCase for array keys
                $cleanKey = str_replace($prefix, '', $key);
                $result[$cleanKey] = $value;
            }
        }

        return $result;
    }

    public static function getAll(): array
    {
        $settings = FrameworcSetting::instance();
        $result = $settings->toArray();

        return $result['wrapper'];
    }
}
