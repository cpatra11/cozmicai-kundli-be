<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private array $details;

    public function __construct(int $statusCode, string $message = '', array $details = [], \Throwable $previous = null, array $headers = [])
    {
        $this->details = $details;
        parent::__construct($statusCode, $message, $previous, $headers, $statusCode);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}