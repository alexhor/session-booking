<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
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

    protected function getViewData(): array
    {
        return [
            'messages' => $this->getSessionMessages(),
            'configs' => $this->getPublicConfigData(),
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
}
