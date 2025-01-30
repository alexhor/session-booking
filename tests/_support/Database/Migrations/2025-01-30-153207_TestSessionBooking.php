<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-01-29-195635_SessionBookingMigration.php');
use App\Database\Migrations\SessionBookingMigration;


class TestSessionBookingMigration extends SessionBookingMigration
{
    protected $DBGroup = 'tests';
}
