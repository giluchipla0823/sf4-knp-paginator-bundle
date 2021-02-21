<?php

namespace App\Repository\Publisher;

use App\Entity\Publisher;

interface PublisherRepositoryInterface
{
    public function findOrFail(int $id): ?Publisher;

    public function create(array $params): Publisher;
}