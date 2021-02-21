<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestHttpException extends BaseHttpException
{
    public function __construct(string $message = "", array $data = [])
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $data);
    }
}