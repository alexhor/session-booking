<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): string
    {
        return view('index');
    }

    public function admin(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin');
    }
}
