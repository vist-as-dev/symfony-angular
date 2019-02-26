<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class UserNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'User not found', 'UserNotFound');
    }
}