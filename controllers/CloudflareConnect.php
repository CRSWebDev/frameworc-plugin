<?php namespace CRSCompany\FrameworC\Controllers;

use Backend\Classes\Controller;
use CRSCompany\FrameworC\Models\FrameworcSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use October\Rain\Support\Facades\Input;

class CloudflareConnect extends Controller
{
    private const CLOUDFLARE_API_BASE = 'https://api.cloudflare.com/client/v4';

    public function index()
    {
        $settings = FrameworcSetting::instance();
        $wrapper = (array) ($settings->wrapper ?? []);

        $defaultDomain = parse_url((string) config('app.url'), PHP_URL_HOST) ?: request()->getHost();
        $storedDomains = (string) ($wrapper['integration_turnstile_domains'] ?? '');

        $this->vars['formDefaults'] = [
            'account_id' => (string) ($wrapper['integration_turnstile_account_id'] ?? ''),
            'api_token' => '',
            'widget_name' => (string) ($wrapper['integration_turnstile_widget_name'] ?? 'frameworc-form'),
            'domains' => $storedDomains !== '' ? $storedDomains : $defaultDomain,
            'save_credentials' => true,
        ];

        $this->vars['currentSiteKey'] = (string) ($wrapper['integration_altcha_key'] ?? '');
    }

    public function onProvisionTurnstile()
    {
        $data = [
            'account_id' => trim((string) Input::get('account_id')),
            'api_token' => trim((string) Input::get('api_token')),
            'widget_name' => trim((string) Input::get('widget_name')),
            'domains' => (string) Input::get('domains'),
            'save_credentials' => (bool) Input::get('save_credentials'),
        ];

        $errors = [];

        if ($data['account_id'] === '') {
            $errors['account_id'] = 'Cloudflare Account ID is required.';
        }

        if ($data['api_token'] === '') {
            $errors['api_token'] = 'Cloudflare API token is required.';
        }

        if ($data['widget_name'] === '') {
            $errors['widget_name'] = 'Widget name is required.';
        }

        $domains = $this->parseDomains($data['domains']);
        if (empty($domains)) {
            $errors['domains'] = 'At least one domain is required.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->verifyToken($data['api_token'], $data['account_id']);

        $widget = $this->findWidgetByName($data['account_id'], $data['api_token'], $data['widget_name']);

        if ($widget) {
            $widget = $this->updateWidgetDomains(
                $data['account_id'],
                $data['api_token'],
                (string) $widget['sitekey'],
                $data['widget_name'],
                $domains
            );
        } else {
            $widget = $this->createWidget(
                $data['account_id'],
                $data['api_token'],
                $data['widget_name'],
                $domains
            );
        }

        $siteKey = (string) ($widget['sitekey'] ?? '');
        $secretKey = (string) ($widget['secret'] ?? '');

        if ($siteKey === '') {
            throw new ApplicationException('Cloudflare did not return a site key.');
        }

        if ($secretKey === '') {
            $widgetDetails = $this->getTurnstileWidget($data['account_id'], $data['api_token'], $siteKey);
            $secretKey = (string) ($widgetDetails['secret'] ?? '');
        }

        if ($secretKey === '') {
            throw new ApplicationException(
                'Cloudflare did not return a secret key. Please verify widget permissions and try again.'
            );
        }

        $this->storeTurnstileSettings($siteKey, $secretKey, $data, $domains);

        Flash::success('Cloudflare Turnstile keys were provisioned and saved.');

        return redirect()->to('/admin/system/settings/update/crscompany/frameworc/settings#primarytab-integrace');
    }

    private function verifyToken(string $apiToken, string $accountId): void
    {
        $accountResponse = Http::withToken($apiToken)
            ->acceptJson()
            ->get(self::CLOUDFLARE_API_BASE . "/accounts/{$accountId}/tokens/verify");

        $accountPayload = $accountResponse->json();

        if ($accountResponse->ok() && ($accountPayload['success'] ?? false)) {
            return;
        }

        $userResponse = Http::withToken($apiToken)
            ->acceptJson()
            ->get(self::CLOUDFLARE_API_BASE . '/user/tokens/verify');

        $userPayload = $userResponse->json();

        if ($userResponse->ok() && ($userPayload['success'] ?? false)) {
            return;
        }

        $accountError = $this->extractCloudflareError($accountPayload);
        $userError = $this->extractCloudflareError($userPayload);

        throw new ApplicationException(
            'Cloudflare token verification failed. '
            . ($accountError ? "Account verify: {$accountError}. " : '')
            . ($userError ? "User verify: {$userError}." : '')
        );
    }

    private function extractCloudflareError(?array $payload): string
    {
        $errors = (array) ($payload['errors'] ?? []);
        if (!empty($errors) && !empty($errors[0]['message'])) {
            return (string) $errors[0]['message'];
        }

        return '';
    }

    private function findWidgetByName(string $accountId, string $apiToken, string $widgetName): ?array
    {
        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->get(self::CLOUDFLARE_API_BASE . "/accounts/{$accountId}/challenges/widgets");

        $payload = $response->json();

        if (!$response->ok() || !($payload['success'] ?? false)) {
            $this->throwCloudflareError($payload, 'Unable to list Turnstile widgets for this account.');
        }

        $widgets = (array) ($payload['result'] ?? []);

        foreach ($widgets as $widget) {
            if (($widget['name'] ?? '') === $widgetName) {
                return $widget;
            }
        }

        return null;
    }

    private function createWidget(string $accountId, string $apiToken, string $widgetName, array $domains): array
    {
        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->post(self::CLOUDFLARE_API_BASE . "/accounts/{$accountId}/challenges/widgets", [
                'name' => $widgetName,
                'mode' => 'managed',
                'domains' => $domains,
            ]);

        $payload = $response->json();

        if (!$response->ok() || !($payload['success'] ?? false)) {
            $this->throwCloudflareError($payload, 'Unable to create Turnstile widget.');
        }

        return (array) ($payload['result'] ?? []);
    }

    private function updateWidgetDomains(
        string $accountId,
        string $apiToken,
        string $siteKey,
        string $widgetName,
        array $domains
    ): array {
        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->put(self::CLOUDFLARE_API_BASE . "/accounts/{$accountId}/challenges/widgets/{$siteKey}", [
                'name' => $widgetName,
                'mode' => 'managed',
                'domains' => $domains,
            ]);

        $payload = $response->json();

        if (!$response->ok() || !($payload['success'] ?? false)) {
            $this->throwCloudflareError($payload, 'Unable to update Turnstile widget.');
        }

        return (array) ($payload['result'] ?? []);
    }

    private function getTurnstileWidget(string $accountId, string $apiToken, string $siteKey): array
    {
        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->get(self::CLOUDFLARE_API_BASE . "/accounts/{$accountId}/challenges/widgets/{$siteKey}");

        $payload = $response->json();

        if (!$response->ok() || !($payload['success'] ?? false)) {
            $this->throwCloudflareError($payload, 'Unable to fetch Turnstile widget details.');
        }

        return (array) ($payload['result'] ?? []);
    }

    private function throwCloudflareError(?array $payload, string $fallbackMessage): void
    {
        $errors = (array) ($payload['errors'] ?? []);
        if (!empty($errors) && !empty($errors[0]['message'])) {
            throw new ApplicationException((string) $errors[0]['message']);
        }

        throw new ApplicationException($fallbackMessage);
    }

    private function parseDomains(string $domains): array
    {
        $parts = preg_split('/[\r\n,]+/', $domains) ?: [];
        $normalized = [];

        foreach ($parts as $part) {
            $value = strtolower(trim($part));

            if ($value === '') {
                continue;
            }

            $value = preg_replace('#^https?://#', '', $value);
            $value = explode('/', $value)[0];

            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function storeTurnstileSettings(string $siteKey, string $secretKey, array $input, array $domains): void
    {
        $settings = FrameworcSetting::instance();
        $wrapper = (array) ($settings->wrapper ?? []);

        $wrapper['integration_captcha_variant'] = 'turnstile';
        $wrapper['integration_altcha_key'] = $siteKey;
        $wrapper['integration_altcha_secret'] = $secretKey;
        $wrapper['integration_turnstile_widget_name'] = $input['widget_name'];
        $wrapper['integration_turnstile_domains'] = implode("\n", $domains);
        $wrapper['integration_turnstile_account_id'] = $input['account_id'];

        if ($input['save_credentials']) {
            $wrapper['integration_turnstile_api_token_encrypted'] = Crypt::encryptString($input['api_token']);
        }

        $settings->wrapper = $wrapper;
        $settings->save();
    }
}
