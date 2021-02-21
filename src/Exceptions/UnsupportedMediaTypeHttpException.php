<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnsupportedMediaTypeHttpException extends BaseHttpException
{
    public function __construct(string $message = "", array $data = [])
    {
        parent::__construct(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $data);
    }
}