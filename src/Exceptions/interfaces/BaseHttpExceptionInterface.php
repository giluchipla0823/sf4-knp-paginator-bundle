<?php

namespace App\Exceptions\interfaces;

use Throwable;

interface BaseHttpExceptionInterface extends Throwable
{
    public function getStatusCode(): int;

    public function getData(): array;

    public function getErrors(): array;
}