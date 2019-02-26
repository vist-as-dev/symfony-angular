<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class PasswordInvalidException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'Password invalid', 'PasswordInvalid');
    }
}