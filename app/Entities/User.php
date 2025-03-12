<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    protected $returnType     = User::class;
    
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['email'] = $this->email;
        return $data;
    }
}
