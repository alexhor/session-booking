<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\MagicLinkController;
use App\Authenticators\Session;

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

    public function addToGroup($userId = null, $group = null)
    {
        if (auth()->user() == null || !auth()->user()->can('users.groups.update')) {
            return $this->failUnauthorized();
        }

        $user = auth()->getProvider()->findById($userId);
        if ($user) {
            if ($user->addGroup($group)) {
                return $this->respond(lang('Validation.user.add_to_group', [$group]));
            }
            return $this->fail(lang('Validation.user.add_to_group_failed', [$group]));
        }
        return $this->failNotFound(lang('Validation.user.id.not_found'));
    }

    public function removeFromGroup($userId = null, $group = null)
    {
        if (auth()->user() == null || !auth()->user()->can('users.groups.update')) {
            return $this->failUnauthorized();
        }

        $user = auth()->getProvider()->findById($userId);
        if ($user) {
            if ($user->removeGroup($group)) {
                return $this->respond(lang('Validation.user.remove_from_group', [$group]));
            }
            return $this->fail(lang('Validation.user.remove_from_group_failed', [$group]));
        }
        return $this->failNotFound(lang('Validation.user.id.not_found'));
    }

    /**
     * Handles the GET request from the email
     */
    public function verify() {
        if (auth()->user()) auth()->logout();
        $magicLinkController = new MagicLinkController();
        $magicLinkController->initController($this->request, $this->response, $this->logger);
        $response = $magicLinkController->verify();
        // Set remember me
        if (auth()->user()) {
            $sessionAuthenticator = new Session(auth()->getProvider());
            $sessionAuthenticator->issueRememberMeToken();
            $response = $response->withCookies();
        }
        return $response;
    }
}
