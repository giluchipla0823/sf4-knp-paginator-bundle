<?php

namespace App\Services\Author;

use App\Entity\Author;
use App\Repository\Author\AuthorRepositoryInterface;

class AuthorService
{
    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    public function __construct(AuthorRepositoryInterface $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    public function all(): array {
        return $this->authorRepository->all();
    }

    /**
     * Gets an author by id.
     *
     * @param int $id
     * @return Author|null
     */
    public function findOrFail(int $id): ?Author {
        return $this->authorRepository->findOrFail($id);
    }

    /**
     * Create an Author.
     *
     * @param array $params
     * @return Author
     */
    public function create(array $params): Author {
        return $this->authorRepository->create($params);
    }
}