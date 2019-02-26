<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class UserClassNotSupportedException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct(403, $message, 'UserClassNotSupported');
    }
}