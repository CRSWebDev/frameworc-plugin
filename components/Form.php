<?php namespace CRSCompany\FrameworC\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Log;
use October\Rain\Exception\AjaxException;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Input;
use October\Rain\Support\Facades\Mail;
use Tailor\Models\EntryRecord;
use Validator;
use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Altcha;
use CRSCompany\FrameworC\Models\FrameworcSetting;
use Illuminate\Support\Facades\Http;
use CRSCompany\FrameworC\Classes\SettingsHelper;

/**
 * Form Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Form extends ComponentBase
{
    private $settings;

    public function componentDetails()
    {
        return [
            'name' => 'Form Component',
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

    public function init() {
        $this->settings = SettingsHelper::getByPrefix('integration_');
    }

    public function onSubmit() {
        $data = Input::all();

        $altcha = new Altcha($this->settings['altcha_secret']);

        $captchaOk = $altcha->verifySolution($data['altcha'], true);

        if (!$captchaOk) {
            throw new ValidationException(['altcha' => __('form.captcha.error')]);
        }

        $formId = $data['_form_id'];
        $formTrueId = $data['_form_true_id'];
        $formElement = '#frameworc-form-' . $formId;
        $formErrorElement = '#frameworc-form-error-' . $formId;

        $post = EntryRecord::inSection('Inquiry');
        $file = null;


        $entry = EntryRecord::inSection('Form')->where('id', $formTrueId)->first();

        if (!$entry) {
            throw new ValidationException(['error' => 'Formulář nebyl nalezen.']);
        }

        $rules = [];
        $messages = [];

        foreach ($entry->fwcFields as $field) {
            if ($field->required) {
                $rules[$field->name] = 'required';

                $messages[$field->name . '.required'] = __('form.field_is_required');

                if ($field->content_group == 'email') {
                    $rules[$field->name] = 'required|email';

                    $messages[$field->name . '.email'] = __('form.enter_valid_email');
                }
            }

            if ($field->content_group == 'file') {
                if (!empty($data[$field->name])) {
                    $post->files = files($field->name);
                    $file = $data[$field->name];
                }

                unset($data[$field->name]);
            }

            if ($field->content_group == 'checkbox' && !empty($data[$field->name])) {
                $data[$field->name] = implode(', ', $data[$field->name]);
            }
        }

        $valid = Validator::validate($data, $rules, $messages);

        $ignoredFields = [
            '_handler',
            '_session_key',
            '_token',
            '_form_id',
            '_form_true_id',
            'altcha'
        ];

        $data = array_filter($data, function($key) use ($ignoredFields) {
            return (!in_array($key, $ignoredFields));
        }, ARRAY_FILTER_USE_KEY);


        $post->form = $formTrueId;
        $post->title = $this->getInquiryTitle($data);
        $post->inquiry = json_encode($data);
        $post->save();

        // Check if n8n webhook URL is configured and send request
        $n8nWebhookUrl = $this->settings['n8n_webhook_url'];
        if (!empty($n8nWebhookUrl)) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-FWC-Auth' => $this->settings['n8n_auth'],
                ])
                ->timeout(30)
                ->post($n8nWebhookUrl, [
                    'form_id' => $formTrueId,
                    'form_data' => $data,
                    'timestamp' => now()->toISOString(),
                    'inquiry_id' => $post->id,
                    'source' => 'frameworc-form',
                    'form_title' => $entry->title ?? 'Unknown Form'
                ]);
            } catch (\Exception $e) {
                // Silent fail - webhook request failed
            }
        }

        // Skip sending emails if n8n webhook URL is configured
        if (empty($this->settings['n8n_webhook_url'])) {
            if (empty($entry->recipients)) { // if no recipients, skip sending email
                return [
                    $formElement => $this->renderPartial('@success')
                ];
            }
            $formItems = [];
            foreach ($entry->fwcFields as $field) {
                if (isset($data[$field->name])) {
                    $formItems[] = [
                        'label' => $field->label,
                        'value' => $data[$field->name],
                    ];
                }
            }

            $emailVars = [
                'header' => __('form.email_header'),
                'footer' => __('form.email_footer'),
                'formItems' => $formItems,
            ];

            Mail::send('crscompany.frameworc::mail.templates.form-backend', $emailVars, function($message) use ($file, $entry) {
                $isFirstLoop = true;
                foreach ($entry->recipients as $recipient) {
                    if ($isFirstLoop) {
                        $message->to($recipient);
                        $isFirstLoop = false;
                    } else {
                        $message->cc($recipient);
                    }
                }

                $message->subject(__('form.email_backend_subject'));

                if (!empty($file)) {
                    $message->attach($file, [
                        'as' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType(),
                    ]);
                }
            });

            if (!empty($data['email'])) {
                Mail::send('crscompany.frameworc::mail.templates.form-client', $emailVars, function($message) use ($data) {
                    $message->to($data['email']);
                    $message->subject(__('form.email_client_subject'));
                });
            }
        }

        return [
            $formElement => $this->renderPartial('@success')
        ];
    }

    private function getInquiryTitle($data) {
        if (isset($data['name'])) {
            $title = $data['name'];
        }
        if (isset($data['email'])) {
            $title = $data['email'];
        }
        if (isset($data['phone'])) {
            $title = $data['phone'];
        }

        if (empty($title)) {
            $title = date('Y-m-d H:i:s');
        } else {
            $title .= ' - ' .  date('Y-m-d H:i:s');
        }

        return $title;
    }

    public static function onCaptcha() {
        $secret = SettingsHelper::getByPrefix('integration')['altcha_secret'];

        $altcha = new Altcha($secret);

        $options = new ChallengeOptions(
            maxNumber: 50000,
            // This sets the 'expires' option to a DateTimeImmutable object representing the current time plus 10 minutes.
            expires: (new \DateTimeImmutable())->add(new \DateInterval('PT10M')),
        );

        $challenge = $altcha->createChallenge($options);

        return json_decode(json_encode($challenge), true);
    }
}
