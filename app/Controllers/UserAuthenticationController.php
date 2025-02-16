<?php

namespace App\Controllers;

use App\Models\UserAuthentication;
use App\Models\User;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Helpers\UserHelper;

class UserAuthenticationController extends ResourceController
{
    private $user_authentication;
    private $user;
	private $session;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
		$this->user_authentication = new UserAuthentication();
		$this->user = new User();
        $this->session = service('session');
    }

    /**
     * Create a new token for given user
     *
     * @return ResponseInterface
     */
    public function create_token()
    {
        $validation = $this->validate([
            'email' => 'required|valid_email|is_not_unique[users.email]',
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Get user data from email
        $email = $this->request->getVar('email');
        $user_data = $this->user->where('email', $email)->findAll();
        if (1 != count($user_data)) return $this->fail('Sorry! no user found');
        $user_data = $user_data[0];

        // Generate token
        $token = $this->user_authentication->insert(['user_id' => $user_data['id']]);
        $link = url_to('Home::login') . '?email=' . urlencode($email) . '&token=' . urlencode($token);
        
        // Send token email
        $email = service('email');
        $email->setTo($user_data['email'], $user_data['firstname'] . ' ' . $user_data['lastname']);
        $email->setSubject('Login link');
        $email->setMessage("Click the following link to login:\n$link");
        $email->send();
        //TODO: limit token sending to one email every X minutes
        
        return $this->respond('E-Mail with token was send', 200);
    }

    /**
     * Try to login user with provided token
     *
     * @return ResponseInterface
     */
    public function login_user()
    {
        $validation = $this->validate([
            'token' => [
                'label' => 'Validation.user_authentication.token.label',
                'rules' => 'required|alpha_numeric|exact_length[72]',
                'errors' => [
                    'required' => 'Validation.user_authentication.token.required',
                    'alpha_numeric' => 'Validation.user_authentication.token.alpha_numeric',
                    'exact_length' => 'Validation.user_authentication.token.exact_length',
                ],
            ],
            'email' => [
                'label' => 'Validation.user.email.label',
                'rules' => 'required|valid_email|is_not_unique[users.email]',
                'errors' => [
                    'required' => 'Validation.user.email.required',
                    'valid_email' => 'Validation.user.email.valid',
                    'is_not_unique' => 'Validation.user.email.not_found',
                ],
            ],
        ]);
        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $token = $this->request->getVar('token');
        // Get user data from email
        $email = $this->request->getVar('email');
        $user_data = $this->user->where('email', $email)->findAll();
        if (1 != count($user_data)) {
            return $this->fail('User not found', 400);
        }
        $user_data = $user_data[0];

        // Check token validity
        if ($this->user_authentication->token_valid($user_data['id'], $token)) {
            $this->set_user_logged_in($user_data['id']);
            return $this->respondCreated('Login successful');
        }
        else {
            return $this->fail(lang('Validation.user_authentication.token.invalid_or_expired'), 400);
        }
    }

    private function set_user_logged_in($user_id): void
    {
        $this->session->set('logged_in_user_id', $user_id);
    }

    /**
     * Logout user
     *
     * @return ResponseInterface
     */
    public function logout_user()
    {
        $this->session->remove('admin_logged_in_user_id');
        $this->session->remove('logged_in_user_id');
        $this->session->destroy();
        return redirect()->route('/');
    }

    public function get_loged_in_user() {
        return $this->respond(UserHelper::get_logged_in_user(), 200);
    }
}
