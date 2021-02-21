<?php

namespace App\Controller\Api\Author;

use App\Controller\Api\ApiController;
use App\Entity\Author;
use App\Services\Book\BookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorBookController extends ApiController
{
    /**
     * @var BookService
     */
    private $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Display all books of an specified author.
     *
     * @Route("/authors/{id}/books", name="authors_books", methods={"GET"})
     * @param Request $request
     * @param Author $author
     * @return JsonResponse
     */
    public function indexAction(Request $request, Author $author): JsonResponse {
        $books = $this->bookService->findByAuthor($author);

        return $this->successResponse($books);
    }
}