<?php

namespace App\Repository\Author;

use App\Entity\Author;

interface AuthorRepositoryInterface
{
    public function all(): array;

    public function findOrFail(int $id): ?Author;

    public function create(array $params): Author;
}