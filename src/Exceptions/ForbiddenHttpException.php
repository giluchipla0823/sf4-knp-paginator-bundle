<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenHttpException extends BaseHttpException
{
    public function __construct(string $message = "", array $data = [])
    {
        parent::__construct(Response::HTTP_FORBIDDEN, $message, $data);
    }
}