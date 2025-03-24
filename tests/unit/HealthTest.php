<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Tests\Support\Libraries\ConfigReader;

/**
 * @internal
 */
final class HealthTest extends CIUnitTestCase
{
    public function testIsDefinedAppPath(): void
    {
        $this->assertTrue(defined('APPPATH'));
    }

    public function testBaseUrlHasBeenSet(): void
    {
        $validation = service('validation');
        helper('setting');

        // BaseURL in app/Config/App.php is a valid URL?
        $this->assertTrue(
            $validation->check(setting('App.baseURL'), 'valid_url'),
            'baseURL "' . setting('App.baseURL') . '" in app/Config/App.php is not valid URL',
        );
    }
}
