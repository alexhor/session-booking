<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class AuthTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testUnauthorizedAccessRedirects()
    {
        $caller = $this->getFilterCaller('auth', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }

    public function testAuthorizedAccess()
    {
        session()->set('logged_in_user_id', 123);
        $caller = $this->getFilterCaller('auth', 'before');
        $result = $caller();
        $this->assertEquals(null, $result);
    }
}
