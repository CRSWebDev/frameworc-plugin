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

/**
 * Form Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Form extends ComponentBase
{
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

    public function onRun()
    {
        $this->addCss(['components/form/Form.scss']);
        $this->addJs(['components/form/Form.js']);
    }

    public function onSubmit() {
        $data = Input::all();

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
        $messqaes = [];

        foreach ($entry->fwcFields as $field) {
            if ($field->required) {
                $rules[$field->name] = 'required';

                $messages[$field->name . '.required'] = 'Toto pole je povinné.';

                if ($field->content_group == 'email') {
                    $rules[$field->name] = 'required|email';

                    $messages[$field->name . '.email'] = 'Zadejte prosím platný email.';
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
            '_form_true_id'
        ];

        $data = array_filter($data, function($key) use ($ignoredFields) {
            return (!in_array($key, $ignoredFields));
        }, ARRAY_FILTER_USE_KEY);


        $post->form = $formTrueId;
        $post->title = $this->getInquiryTitle($data);
        $post->inquiry = json_encode($data);
        $post->save();

        $emailVars = [
            'header' => 'Nová zpráva z webu',
            'footer' => 'Jestli vám email došel omylem, neodpovídejte na něj.',
            'formItems' => $data,
        ];

        Mail::send('crs.frameworc::mail.templates.form-backend', $emailVars, function($message) use ($file) {
            $message->to('vojta.klos@gmail.com', 'FrameworC email');

            if (!empty($file)) {
                $message->attach($file, [
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                ]);
            }
        });

        if (!empty($data['email'])) {
            Mail::send('crs.frameworc::mail.templates.form-client', $emailVars, function($message) use ($data) {
                $message->to($data['email'], 'FrameworC email');
            });
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
}
