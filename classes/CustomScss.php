<?php namespace CRSCompany\FrameworC\Classes;

use Cache;
use CRSCompany\FrameworC\Models\FrameworcSiteSetting;
use Log;
use ScssPhp\ScssPhp\Compiler;
use ValidationException;

/**
 * CustomScss compiles the SCSS entered in the global and per-site settings.
 */
class CustomScss
{
    /**
     * compile turns SCSS into CSS, throwing on syntax errors.
     */
    public static function compile(string $scss): string
    {
        return (new Compiler)->compileString($scss)->getCss();
    }

    /**
     * validate compiles the source and rethrows failures as a validation error
     * for the given field, so the backend form refuses to save broken SCSS.
     */
    public static function validate(string $scss, string $field): void
    {
        if (trim($scss) === '') {
            return;
        }

        try {
            static::compile($scss);
        } catch (\Exception $e) {
            throw new ValidationException([$field => 'SCSS se nepodařilo zkompilovat: ' . $e->getMessage()]);
        }
    }

    /**
     * source joins the global SCSS and the active site's SCSS, global first so
     * the site part can use its variables and mixins.
     */
    public static function source(?string $globalScss = null, ?string $siteScss = null): string
    {
        $globalScss ??= SettingsHelper::getByPrefix('styles_')['globalScss'] ?? '';
        $siteScss ??= FrameworcSiteSetting::get('siteScss', '');

        return trim($globalScss . "\n" . $siteScss);
    }

    /**
     * forActiveSite returns the compiled CSS for the active site. A compile
     * error is logged and yields no CSS rather than breaking the page.
     */
    public static function forActiveSite(): string
    {
        $source = static::source();

        if ($source === '') {
            return '';
        }

        try {
            return Cache::rememberForever('frameworc.custom-scss.' . md5($source), function () use ($source) {
                return static::compile($source);
            });
        } catch (\Exception $e) {
            Log::error('FrameworC custom SCSS failed to compile: ' . $e->getMessage());
            return '';
        }
    }
}
