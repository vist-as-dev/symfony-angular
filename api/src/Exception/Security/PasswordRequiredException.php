<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class PasswordRequiredException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'Password required', 'PasswordRequired');
    }
}