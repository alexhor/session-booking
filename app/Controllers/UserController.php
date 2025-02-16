<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\UserHelper;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    private $user;
	private $session;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
		$this->user = new User();
        $this->session = service('session');
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        if (false === UserHelper::get_logged_in_admin()) return $this->failUnauthorized();
        $users = $this->user->findAll();
        return $this->respond($users);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        if (false === UserHelper::get_logged_in_admin() && !UserHelper::is_logged_in_user($id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->find($id);
        if ($user) {
            return $this->respond($user);
        }
        return $this->failNotFound(lang('Validation.user.not_found'));
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $validation = $this->validate([
            'firstname' => [
                'label' => 'Validation.user.firstname.label',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Validation.user.firstname.required',
                ],
            ],
            'lastname' => [
                'label' => 'Validation.user.lastname.label',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Validation.user.lastname.required',
                ],
            ],
            'email' => [
                'label' => 'Validation.user.email.label',
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Validation.user.email.required',
                    'valid_email' => 'Validation.user.email.valid',
                    'is_unique' => 'Validation.user.email.taken',
                ],
            ],
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userData = [
            'firstname' => $this->request->getVar('firstname'),
            'lastname' => $this->request->getVar('lastname'),
            'email' => $this->request->getVar('email')
        ];

        $userId = $this->user->insert($userData);
        if ($userId) {
            $user = $this->user->find($userId);
            return $this->respond([
                'data' => $user,
                'message' => lang('Validation.user.created'),
            ]);
        }
        return $this->fail(lang('Validation.user.creating_failed'));
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        if (false === UserHelper::get_logged_in_admin() && !UserHelper::is_logged_in_user($id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->find($id);
        if ($user) {

            $validation = $this->validate([
                'firstname' => [
                    'label' => 'Validation.user.firstname.label',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Validation.user.firstname.required',
                    ],
                ],
                'lastname' => [
                    'label' => 'Validation.user.lastname.label',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Validation.user.lastname.required',
                    ],
                ],
                'email' => [
                    'label' => 'Validation.user.email.label',
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Validation.user.email.required',
                        'valid_email' => 'Validation.user.email.valid',
                    ],
                ],
            ]);

            if (!$validation) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            // If email should be changed, make sure it's unique
            if ($this->request->getVar('email') != $user['email']) {
                $email_validation = $this->validate([
                    'email' => [
                        'label' => 'Validation.user.email.label',
                        'rules' => 'is_unique[users.email]',
                        'errors' => [
                            'is_unique' => 'Validation.user.email.taken',
                        ],
                    ],
                ]);
                if (!$email_validation) {
                    return $this->failValidationErrors($this->validator->getErrors());
                }
            }

            $user = [
                'id' => $id,
                'firstname' => $this->request->getVar('firstname'),
                'lastname' => $this->request->getVar('lastname'),
                'email' => $this->request->getVar('email')
            ];

            $response = $this->user->save($user);
            if ($response) {
                return $this->respond([
                    'data' => $user,
                    'message' => lang('Validation.user.updated'),
                ]);
            }
            return $this->fail(lang('Validation.user.updating_failed'));
        }
        return $this->failNotFound(lang('Validation.user.id.not_found'));
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        if (false === UserHelper::get_logged_in_admin() && !UserHelper::is_logged_in_user($id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->find($id);
        if ($user) {
            $response = $this->user->where('id', $id)->delete();
            if ($response) {
                return $this->respond([
                    'data' => $user,
                    'message' => lang('Validation.user.deleted'),
                ]);
            }
            return $this->fail(lang('Validation.user.deleting_failed'));
        }
        return $this->failNotFound(lang('Validation.user.id.not_found'));
    }
}
