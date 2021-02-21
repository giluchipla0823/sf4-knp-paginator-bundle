<?php

namespace App\Controller\Api\Author;

use App\Controller\Api\ApiController;
use App\Entity\Author;
use App\Services\Author\AuthorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends ApiController
{

    /**
     * @var AuthorService
     */
    private $authorService;

    public function __construct(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    /**
     * Display all books.
     *
     * @Route("/authors", name="authors", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse {
        $authors = $this->authorService->all();

        return $this->successResponse($authors);
    }

    /**
     * Display an author.
     *
     * @Route("/authors/{id}", name="authors_show", methods={"GET"})
     * @param Author $author
     * @return JsonResponse
     */
    public function showAction(Author $author): JsonResponse{
        return $this->successResponse($author);
    }
}