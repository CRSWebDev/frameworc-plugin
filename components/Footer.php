<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;
use Illuminate\Support\Facades\Http;
use October\Rain\Support\Facades\Input;
use October\Rain\Exception\AjaxException;
use CRSCompany\FrameworC\Classes\SettingsHelper;
use AltchaOrg\Altcha\Altcha;
use October\Rain\Exception\ValidationException;
use Validator;

/**
 * Footer Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Footer extends ComponentBase
{
    private $settings;

    public function init() {
        $this->settings = SettingsHelper::getByPrefix('integration_');
    }

    public function componentDetails()
    {
        return [
            'name' => 'Footer Component',
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

    public function getNav() {
        $nav = EntryRecord::inSection('Footer')
            ->first();

        return $nav;
    }

    public function getMenu($nav) {
        $menu = [];
        foreach ($nav ?? [] as $item) {
            if ($item['parent_id'] == null) {
                $menu[$item->id] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank,
                    'children' => []
                ];
            } else {
                $menu[$item->parent_id]['children'][] = [
                    'title' => $item->title,
                    'url' => $item->url,
                    'blank' => $item->blank
                ];
            }
        }

        return $menu;
    }

    public function onNewsletterSubmit() {
        $email = Input::get('email');

        Validator::validate(['email' => $email], ['email' => 'required|email'], [
            'email.required' => __('form.field_is_required'),
            'email.email' => __('form.enter_valid_email'),
        ]);

        // The newsletter form always renders the altcha widget
        $altcha = new Altcha($this->settings['altcha_secret'] ?? '');

        if (!$altcha->verifySolution(Input::get('altcha', ''), true)) {
            throw new ValidationException(['altcha' => __('form.captcha.error')]);
        }

        // Call n8n webhook for newsletter subscription
        $webhookUrl = $this->settings['n8n_newsletter_webhook_url'] ?? null;

        if (!$webhookUrl) {
            throw new AjaxException('N8N webhook URL is not set');
        }

        $response = Http::withHeaders([
            'X-FWC-AUTH' => $this->settings['n8n_auth'] ?? ''
        ])->post($webhookUrl, [
            'email' => $email
        ]);

        if ($response->getStatusCode() == 200) {
            return [
                'success' => true
            ];
        } else {
            return throw new AjaxException('Something went wrong. Please try again.');
        }
    }
}
