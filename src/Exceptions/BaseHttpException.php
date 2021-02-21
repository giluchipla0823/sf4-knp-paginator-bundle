<?php

namespace App\Exceptions;

use App\Exceptions\interfaces\BaseHttpExceptionInterface;
use RuntimeException;
use Throwable;

class BaseHttpException extends RuntimeException implements BaseHttpExceptionInterface
{

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array $data
     */
    private $data;

    public function __construct(int $statusCode, string $message = "", array $data = [], Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;

        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getData(): array
    {
        return $this->data;
    }


    public function getErrors(): array
    {
        return [];
    }
}