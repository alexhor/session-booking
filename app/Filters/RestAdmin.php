<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Helpers\UserHelper;

class RestAdmin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (false === UserHelper::get_logged_in_admin()) {
            return service('response')->setStatusCode(401);
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}