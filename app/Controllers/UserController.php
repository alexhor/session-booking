<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Entities\User;
use Config\Validation;

class UserController extends ResourceController
{
    private $user;
	private $session;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
		$this->user = auth()->getProvider();
        $this->session = service('session');
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        if (!auth()->user() || !auth()->user()->can('users.show')) return $this->failUnauthorized();
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
        if (auth()->user() == null || !(auth()->user()->can('users.show') || user_id() == $id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->findById($id);
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
                'rules' => [
                    'required',
                    'max_length[254]',
                    'valid_email',
                    'is_unique[auth_identities.secret]',
                ],
                'errors' => [
                    'required' => 'Validation.user.email.required',
                    'valid_email' => 'Validation.user.email.valid',
                    'is_unique' => 'Validation.user.email.taken',
                    'max_length' => 'Validation.user.email.too_long',
                ],
            ],
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userEntityObject = new User([
            'firstname' => $this->request->getVar('firstname'),
            'lastname' => $this->request->getVar('lastname'),
            'email' => $this->request->getVar('email')
        ]);

        $is_first_user = 0 == count($this->user->findAll());
        
        if ($this->user->save($userEntityObject)) {
            $user = $this->user->findById($this->user->getInsertID());
            $this->user->addToDefaultGroup($user);
            if ($is_first_user) {
                $user->addGroup('admin');
            }
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
        if (auth()->user() == null || !(auth()->user()->can('users.update') || user_id() == $id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->findById($id);
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
                    'rules' => [
                        'required',
                        'max_length[254]',
                        'valid_email',
                        
                    ],
                    'errors' => [
                        'required' => 'Validation.user.email.required',
                        'valid_email' => 'Validation.user.email.valid',
                        'max_length' => 'Validation.user.email.too_long',
                    ],
                ],
            ]);

            if (!$validation) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            // If email should be changed, make sure it's unique
            if ($this->request->getVar('email') != $user->email) {
                $email_validation = $this->validate([
                    'email' => [
                        'label' => 'Validation.user.email.label',
                        'rules' => 'is_unique[auth_identities.secret]',
                        'errors' => [
                            'is_unique' => 'Validation.user.email.taken',
                        ],
                    ],
                ]);
                if (!$email_validation) {
                    return $this->failValidationErrors($this->validator->getErrors());
                }
            }

            $user->fill([
                'firstname' => $this->request->getVar('firstname'),
                'lastname' => $this->request->getVar('lastname'),
                'email' => $this->request->getVar('email')
            ]);

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
        if (auth()->user() == null || !(auth()->user()->can('users.delete') || user_id() == $id)) {
            return $this->failUnauthorized();
        }

        $user = $this->user->findById($id);
        if ($user) {
            if ($this->user->delete($id, true)) {
                if (user_id() == $id) {
                    auth()->logout();
                    session()->destroy();
                }
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
