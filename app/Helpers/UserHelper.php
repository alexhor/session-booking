<?php

namespace App\Helpers;

class UserHelper
{
    public static function get_logged_in_user(): string|false
    {
        $session = service('session');
        if ($session->has('logged_in_user_id')) return $session->get('logged_in_user_id');
        else return false;
    }
    public static function is_logged_in_user($user_id): bool
    {
        $session = service('session');
        if ($session->has('logged_in_user_id')) return $session->get('logged_in_user_id') == $user_id;
        else return false;
    }
}
