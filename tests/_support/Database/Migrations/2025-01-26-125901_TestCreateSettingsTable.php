<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'vendor/codeigniter4/settings/src/Database/Migrations/2021-07-04-041948_CreateSettingsTable.php');
use CodeIgniter\Settings\Database\Migrations\CreateSettingsTable;


class TestCreateSettingsTable extends CreateSettingsTable
{
    protected $DBGroup = 'tests';
}
