<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class SettingController extends ResourceController
{
    public function __construct()
    {
        helper('setting');
    }

    public function get($key = null)
    {
        if (!in_array($key, setting('App.apiPublicSettingKeys')) && (!auth()->user() || !auth()->user()->can('settings.show'))) {
            return $this->failUnauthorized();
        }

        if (!array_key_exists($key, setting('App.apiAllowedSettingKeys'))) {
            return $this->failForbidden();
        }

        $responseData = [
            'value' => setting($key),
        ];

        $valueTypeOrValidValueArray = setting('App.apiAllowedSettingKeys')[$key];
        if (is_callable($valueTypeOrValidValueArray)) {
            $valueTypeOrValidValueArray = $valueTypeOrValidValueArray();
        }
        $responseData['validation'] = $valueTypeOrValidValueArray;

        return $this->respond($responseData);
    }

    public function set($key = null)
    {
        if (!auth()->user() || !auth()->user()->can('settings.update')) {
            return $this->failUnauthorized();
        }

        if (!array_key_exists($key, setting('App.apiAllowedSettingKeys'))) {
            return $this->failForbidden();
        }
        $value = $this->request->getVar('value');
        
        $valueTypeOrValidValueArray = setting('App.apiAllowedSettingKeys')[$key];
        if (is_callable($valueTypeOrValidValueArray)) {
            $valueTypeOrValidValueArray = $valueTypeOrValidValueArray();
        }
        if (is_array($valueTypeOrValidValueArray)) {
            if (!in_array($value, $valueTypeOrValidValueArray)) {
                return $this->fail(lang('Validation.setting_invalid_value'));
            }
        }
        else if ('email' == $valueTypeOrValidValueArray) {
            $validation = service('validation');
            $validation->setRule('email', 'email', 'valid_email');
            if (!$validation->run(['email' => $value])) {
                return $this->fail(lang('Validation.setting_invalid_value'));
            }
        }
        else if ('password' == $valueTypeOrValidValueArray || \string::class == $valueTypeOrValidValueArray) {
            $value = strval($value);
        }
        else if ('timestamp' == $valueTypeOrValidValueArray) {
            if (is_numeric($value)) {
                $value = intval($value);
            }
            else if ('now' != $value) {
                return $this->fail(lang('Validation.setting_invalid_value'));
            }
        }
        else if (\integer::class == $valueTypeOrValidValueArray) {
            if (is_numeric($value)) {
                $value = intval($value);
            }
            else {
                return $this->fail(lang('Validation.setting_invalid_value'));
            }
        }
        else {
            if (gettype($value) != $valueTypeOrValidValueArray) {
                return $this->fail(lang('Validation.setting_invalid_value'));
            }
        }

        if (null == $value) {
            if (\string::class == $valueTypeOrValidValueArray || (is_array($valueTypeOrValidValueArray) && in_array('', $valueTypeOrValidValueArray))) {
                $value = '';
            }
            else {
                return $this->failNotFound();
            }
        }
        
        setting($key, $value);
        if (setting($key) == $value) {
            return $this->respond(200);
        }
        else {
            return $this->fail(lang('Validation.setting_saving_failed'));
        }
    }

    public function delete($key = null)
    {
        if (!auth()->user() || !auth()->user()->can('settings.delete')) {
            return $this->failUnauthorized();
        }

        if (!array_key_exists($key, setting('App.apiAllowedSettingKeys'))) {
            return $this->failForbidden();
        }

        setting()->forget($key);
        return $this->respondDeleted($key);
    }

    public function getWithValidationData()
    {
        if (!auth()->user() || !auth()->user()->can('settings.update')) {
            return $this->failUnauthorized();
        }
        
        $settingsList = [];
    
        foreach (setting('App.apiAllowedSettingKeys') as $settingKey => $validation) {
            if (is_callable($validation)) $validation = $validation();
            $setting = [
                'key' => $settingKey,
                'value' => setting($settingKey),
                'validation' => $validation,
            ];
            $settingsList[$settingKey] = $setting;
        }
    
        return $this->respond($settingsList, 200);
    }

    public function getEmailTempate($lang = null, $templateName = null)
    {
        if (!auth()->user() || !auth()->user()->can('emails.show')) {
            return $this->failUnauthorized();
        }

        $langContext = 'lang:' . $lang;
        $template = setting()->get('Email.' . $templateName . 'TemplateJson', $langContext);
        if (null == $template) return $this->failNotFound();
        
        $template = json_decode($template);
        $template->subject = setting()->get('Email.' . $templateName . 'TemplateSubject', $langContext);
        $template->subjectDisabled = setting('Email.' . $templateName . 'TemplateSubjectDisabled');

        return $this->respond($template, 200);
    }

    public function saveEmailTempate($lang = null, $templateName = null)
    {
        if (!auth()->user() || !auth()->user()->can('emails.update')) {
            return $this->failUnauthorized();
        }
        
        // Validate data
        $validation = $this->validate([
            'json' => [
                'label' => 'Validation.email_templates.json.label',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Validation.email_templates.json.required',
                    'valid_json' => 'Validation.email_templates.json.valid_json',
                ],
            ],
            'html' => [
                'label' => 'Validation.email_templates.html.label',
                'rules' => 'required|string',
                'errors' => [
                    'required' => 'Validation.email_templates.html.required',
                    'string' => 'Validation.email_templates.html.string',
                ],
            ],
            'subject' => [
                'label' => 'Validation.email_templates.subject.label',
                'rules' => 'required|string',
                'errors' => [
                    'required' => 'Validation.email_templates.subject.required',
                    'string' => 'Validation.email_templates.subject.string',
                ],
            ],
        ]);
        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        
        $templateJson = $this->request->getVar('json');
        $templateHtml = $this->request->getVar('html');
        $templateSubject = $this->request->getVar('subject');
        
        $langContext = 'lang:' . $lang;
        setting()->set('Email.' . $templateName . 'TemplateJson', $templateJson, $langContext);
        setting()->set('Email.' . $templateName . 'TemplateHtml', $templateHtml, $langContext);
        setting()->set('Email.' . $templateName . 'TemplateSubject', $templateSubject, $langContext);

        $success = setting()->get('Email.' . $templateName . 'TemplateJson', $langContext) == $templateJson && setting()->get('Email.' . $templateName . 'TemplateHtml', $langContext) == $templateHtml && setting()->get('Email.' . $templateName . 'TemplateSubject', $langContext) == $templateSubject;
        return $success ? $this->respond(lang('Validation.email_templates.success'), 200) : $this->fail(lang('Validation.failed_to_save_email_template'));
    }

    public function resetEmailTempate($lang = null, $templateName = null)
    {
        if (!auth()->user() || !auth()->user()->can('emails.delete')) {
            return $this->failUnauthorized();
        }

        $langContext = 'lang:' . $lang;
        setting()->forget('Email.' . $templateName . 'TemplateJson', $langContext);
        setting()->forget('Email.' . $templateName . 'TemplateHtml', $langContext);
        setting()->forget('Email.' . $templateName . 'TemplateSubject', $langContext);
        return $this->respondDeleted($templateName);
    }
}
