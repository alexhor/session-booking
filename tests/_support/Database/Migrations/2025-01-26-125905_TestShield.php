<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'vendor/codeigniter4/shield/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php');
use CodeIgniter\Shield\Database\Migrations\CreateAuthTables;


class TestCreateAuthTables extends CreateAuthTables
{
    protected $DBGroup = 'tests';
}
