<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function test(): string
    {
        return view('test', $this->getViewData());
    }

    public function serveVite($path = '')
    {
        $viteUrl = 'http://localhost:5173' . $_SERVER['REQUEST_URI'];
        $client = \Config\Services::curlrequest();
        $response = $client->get($viteUrl, ['http_errors' => false]);
        
        $contentType = $response->getHeader('Content-Type') ? $response->getHeader('Content-Type')->getValue() : 'application/javascript';

        return $this->response
            ->setStatusCode($response->getStatusCode())
            ->setBody($response->getBody())
            ->setHeader('Content-Type', $contentType);
    }

    public function index(): string
    {
        return view('index', $this->getViewData());
    }

    public function adminUsers(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin-users', $this->getViewData());
    }

    public function adminSettings(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin-settings', $this->getSettingsViewData());
    }

    public function admin(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin', $this->getViewData());
    }

    protected function getViewData(): array
    {
        return [
            'messages' => $this->getSessionMessages(),
            'configs' => $this->getPublicConfigData(),
        ];
    }

    protected function getSettingsViewData(): array
    {
        return [
            'messages' => $this->getSessionMessages(),
            'configs' => $this->getConfigData(),
        ];
    }

    protected function getSessionMessages(): array
    {
        $error_list = [];
    
        if (session()->has('error')) {
            array_push($error_list, [
                'message' => session()->get('error'),
                'status' => 400
            ]);
        }
    
        if (session()->has('errors')) {
            foreach (session()->get('errors') as $key => $message) {
                array_push($error_list, [
                    'message' => $message,
                    'status' => 400
                ]);
            }
        }
    
        return $error_list;
    }

    protected function getPublicConfigData(): array
    {
        helper('setting');
        $config = [];
        foreach (setting('App.apiPublicSettingKeys') as $key => $configKey) {
            $config[$configKey] = setting($key);
        }
        return $config;
    }

    protected function getConfigData(): array
    {
        helper('setting');
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
    
        return $settingsList;
    }
}
