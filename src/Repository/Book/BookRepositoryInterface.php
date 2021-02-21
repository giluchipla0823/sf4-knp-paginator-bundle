<?php

namespace App\Repository\Book;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Publisher;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

interface BookRepositoryInterface
{
    public function all(): array;

    public function getCustomQueryData(): array;

    /**
     * @param Request $request
     * @return PaginatorInterface|array
     */
    public function filters(Request $request);

    public function findOrFail(int $id): ?Book;

    public function findByAuthor(Author $author): array;

    public function create(array $params): Book;

    public function queryTransactions(): ?Book;
}