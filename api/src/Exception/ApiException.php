<?php

namespace App\Exception;


use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private $errorCode;

    public function __construct(
        int $statusCode,
        string $message = null,
        string $errorCode = null,
        \Exception $previous = null,
        array $headers = [],
        ?int $code = 0
    )
    {
        $this->errorCode = $errorCode;

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function toArray()
    {
        return [
            'error' => $this->getErrorCode(),
            'message' => $this->getMessage(),
        ];
    }
}