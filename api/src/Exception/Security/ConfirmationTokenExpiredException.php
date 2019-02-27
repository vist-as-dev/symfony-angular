<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class ConfirmationTokenExpiredException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            403,
            'Confirmation token expired',
            'ConfirmationTokenExpired'
        );
    }
}