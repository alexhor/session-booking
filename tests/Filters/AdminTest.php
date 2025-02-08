<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class AdminTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testUnauthorizedAccessRedirects()
    {
        $caller = $this->getFilterCaller('admin', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }

    public function testAuthorizedAccess()
    {
        session()->set('admin_logged_in_user_id', 123);
        $caller = $this->getFilterCaller('admin', 'before');
        $result = $caller();
        $this->assertEquals(null, $result);
    }
}
