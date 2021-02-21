<?php

namespace App\Controller;

use App\Services\Book\BookService;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ExcelTestController extends AbstractController
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
     * @Route("/excel/test", name="excel_test")
     */
    public function index()
    {
        return new JsonResponse(['message' => 'OK']);
    }

    /**
     * Export basic report about books.
     *
     * @Route("/excel/test/report-basic", name="excel_test_report")
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function export(Request $request){
        $request->query->add('export', 'csv');
        $books = $this->bookService->filters($request);

        return $this->bookService->export($books);
    }
}
