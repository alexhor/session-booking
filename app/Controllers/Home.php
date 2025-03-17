<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): string
    {
        return view('index');
    }

    public function adminUsers(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin-users');
    }

    public function adminSettings(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin-settings');
    }

    public function admin(): RedirectResponse|string
    {
        if (!auth()->user() || !auth()->user()->inGroup('admin')) return redirect()->to('/')->with('error', lang('Admin.access_denied'));
        else return view('admin');
    }
}
