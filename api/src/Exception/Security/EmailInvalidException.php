<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class EmailInvalidException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'Email invalid', 'EmailInvalid');
    }
}