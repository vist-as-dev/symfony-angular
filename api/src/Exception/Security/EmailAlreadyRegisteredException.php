<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class EmailAlreadyRegisteredException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'Email already registered', 'EmailAlreadyRegistered');
    }
}