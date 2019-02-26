<?php

namespace App\Exception\Security;


use App\Exception\ApiException;

class AccessDeniedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(403, 'Access denied', 'AccessDenied');
    }
}