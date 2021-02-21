<?php

namespace App\Exceptions;

use App\Helpers\ValidationHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UnprocessableEntityHttpException extends BaseHttpException
{
    /**
     * @var ConstraintViolationListInterface $errors
     */
    private $errors;

    public function __construct(ConstraintViolationListInterface $errors, string $message = "", array $data = [])
    {
        $this->errors = ValidationHelper::formatErrors($errors);

        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message, $data);
    }

    public function getErrors(): array{
        return $this->errors;
    }
}