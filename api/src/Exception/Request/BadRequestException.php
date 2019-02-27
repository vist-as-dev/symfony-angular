<?php

namespace App\Exception\Request;


use App\Exception\ApiException;

class BadRequestException extends ApiException
{
    public function __construct($extra = [])
    {
        parent::__construct(400, 'Bad request', 'BadRequest', $extra);
    }
}