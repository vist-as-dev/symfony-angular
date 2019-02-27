<?php

namespace App\Exception;


use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private $errorCode;
    private $extra;

    public function __construct(
        int $statusCode,
        string $message = null,
        string $errorCode = null,
        array $extra = [],
        \Exception $previous = null,
        array $headers = [],
        ?int $code = 0
    )
    {
        $this->errorCode = $errorCode;
        $this->extra = $extra;

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    public function toArray()
    {
        return [
            'error' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'extra' => $this->getExtra(),
        ];
    }

}