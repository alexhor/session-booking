<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RestAdmin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in_user_id')) {
            return service('response')->setStatusCode(401);
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}