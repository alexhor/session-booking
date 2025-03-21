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
        if (!array_key_exists($key, setting('App.apiPublicSettingKeys')) && (!auth()->user() || !auth()->user()->can('settings.show'))) {
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
}
