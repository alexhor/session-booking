<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-02-05-141500_UserAuthentication.php');
use App\Database\Migrations\UserAuthenticationMigration;


class TestUserAuthenticationMigration extends UserAuthenticationMigration
{
    protected $DBGroup = 'tests';
}
