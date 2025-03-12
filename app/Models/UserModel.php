<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use App\Entities\User;

class UserModel extends ShieldUserModel
{
    protected $returnType = User::class;

    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'firstname', // Added
            'lastname',  // Added
        ];
    }
/*
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $userList = parent::findAll($limit, $offset);
        foreach($userList as $user) {
            d($user->email);
        }
        dd();
    }*/
}
