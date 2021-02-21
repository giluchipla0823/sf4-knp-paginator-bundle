<?php


namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ConflictHttpException extends BaseHttpException
{
    public function __construct($message = "", array $data = [])
    {
        parent::__construct(Response::HTTP_CONFLICT, $message, $data);
    }
}