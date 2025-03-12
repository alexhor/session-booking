<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class UserAuthenticationController extends ResourceController
{
    public function get_logged_in_user() {
        if (auth()->user()) return $this->respond(auth()->user()->id);
        else return $this->respond(false);
    }

    public function is_admin() {
        if (auth()->user()) return $this->respond(auth()->user()->inGroup('admin'));
        else return $this->respond(false);
    }
}
