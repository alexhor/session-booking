<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-01-26-125907_User.php');
use App\Database\Migrations\UserMigration;


class TestUserMigration extends UserMigration
{
    protected $DBGroup = 'tests';
}
