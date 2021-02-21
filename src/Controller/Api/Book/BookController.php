<?php

namespace App\Controller\Api\Book;

use App\Controller\Api\ApiController;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Publisher;
use App\Exceptions\ValidationException;
use App\Helpers\ConstraintsHelper;
use App\Services\Author\AuthorService;
use App\Services\Book\BookService;
use App\Services\Publisher\PublisherService;
use App\Traits\ApiRequestValidation;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception As PhpSpreadsheetException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ORM as ORMAssert;
use App\Validator\Constraints as CustomAssert;
use Throwable;

class BookController extends ApiController
{
    use ApiRequestValidation;

    /**
     * @var BookService
     */
    private $bookService;
    /**
     * @var AuthorService
     */
    private $authorService;
    /**
     * @var PublisherService
     */
    private $publisherService;

    public function __construct(
        BookService $bookService,
        AuthorService $authorService,
        PublisherService $publisherService
    )
    {
        $this->bookService = $bookService;
        $this->authorService = $authorService;
        $this->publisherService = $publisherService;
    }

    /**
     * @Route("/books", name="books", methods={"GET"})
     *
     * @param Request $request
     * @return BinaryFileResponse|JsonResponse
     * @throws PhpSpreadsheetException
     */
    public function indexAction(Request $request) {
        $export = $request->query->get('export');

        $request->query->add(['filters' => $this->getFilters()]);

        $constraints = [
            'filters' => [
                new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Collection([
                        'fields' => [
                            'books' => ConstraintsHelper::createOptionalConstraintToCollection(['title']),
                            'authors' => ConstraintsHelper::createOptionalConstraintToCollection(['name']),
                            'publishers' => ConstraintsHelper::createOptionalConstraintToCollection(['name']),
                        ]
                    ])
                ])
            ]
        ];

        $this->validateRequestData($request->query->all(), $constraints);

        $filters = $this->getFilters();

        $request->request->add(['filters' => $filters]);

        $books = $this->bookService->filters($request);

        if($export === 'csv'){
            return $this->bookService->export($books);
        }

        return $this->successResponse($books);
    }

    /**
     * @Route("/books/custom/list", name="books-custom-list", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customAction(Request $request): JsonResponse {

        $result = $this->bookService->getCustomQueryData();

        return $this->successResponse($result);
    }

    /**
     * Display a book by id.
     *
     * @Route("/books/{id}", name="books_show", methods={"GET"})
     * @param Book $book
     * @return JsonResponse
     */
    public function showAction(Book $book): JsonResponse{
        return $this->successResponse($book);
    }
    
    /**
     * Prueba con transacciones.
     *
     * @Route("/books/store/multiples", name="books_store_multiples", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function storeMultipleAction(Request $request): JsonResponse {
        /* @var Connection $connection */
        $connection = $this->getDoctrine()->getConnection();

        $book = null;

        $connection->transactional(function($em) use (&$book){
            $author = $this->authorService->create([
                'name' => 'Author test 09'
            ]);

            $publisher = $this->publisherService->create([
                'name' => 'Publisher test 09'
            ]);

            $params = [
                'title' => 'Book Gino 9',
                'summary' => 'Summary Gino 9',
                'description' => 'Description Gino 9',
                'quantity' => 10,
                'price' => 5,
                'author' => $author,
                'publisher' => $publisher
            ];

            $book = $this->bookService->create($params);
        });

        return $this->successResponse($book, 'Book, author and publisher created',  Response::HTTP_CREATED);
    }

    /**
     * Create a book with author and publisher.
     *
     * @Route("/books", name="books_store", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function storeAction(Request $request): JsonResponse {
        $constraints = [
            'title' => [
                new Assert\NotBlank(),
                new ORMAssert\UniqueEntity([
                    'entityClass' => Book::class,
                    'defaultColumn' => 'title'
                ])
            ],
            'summary' => [
                new Assert\NotBlank()
            ],
            'description' => [
                new Assert\NotBlank()
            ],
            'quantity' => [
                new Assert\NotBlank()
            ],
            'price' => [
                new Assert\NotBlank()
            ],
            'author_id' => [
                new Assert\NotBlank(),
                new ORMAssert\ExistsEntity([
                    'entityClass' => Author::class
                ])
            ],
            'publisher_id' => [
                new Assert\NotBlank(),
                new ORMAssert\ExistsEntity([
                    'entityClass' => Publisher::class
                ])
            ],
            'test_email' => [
                new Assert\Optional([
                    new CustomAssert\Conditional([
                        'constraints' => [
                            new Assert\NotBlank(),
                            new Assert\Email()
                        ],
                        'condition' => function($value) use($request){
                            return $request->get('publisher_id') === 2;
                        }
                    ])
                ])
            ]
        ];

        $this->validateRequestData($request->request->all(), $constraints);

        // dd($request->request->all());

        // $book = $this->bookService->queryTransactions();

        $book = $this->bookService->create($request->request->all());

        return $this->successResponse($book, 'Book created',  Response::HTTP_CREATED);
    }

    private function getFilters(){
        return [
            'books' => [
                'title' => [
                    [
                        'value' => '',
                    ]
                ]
            ],
            'authors' => [
                'name' => [
                    [
                        'value' => '',
                    ]
                ]
            ],
            'publishers' => [
                'name' => [
                    [
                        'value' => '',
                    ]
                ]
            ],

//            'books' => [
//                // 'name' => []
//            ],
            // 'authors'

//            'book_title' => [
//                [
//                    'value' => 'Voluptatum ab libero sit.'
//                ]
//            ],
//            'book_price' => [
//                [
//                    'value' => 30,
//                    'filter_type' => KnpPaginatorSearch::FILTER_TYPE_EQUAL_TO
//                ]
//            ]
//            'book_price' => [
//                [
//                    'value' => [20, 30],
//                    'filter_type' => KnpPaginatorSearch::FILTER_TYPE_CONTAIN
//                ]
//            ],
        ];
    }
}