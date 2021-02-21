<?php

namespace App\Services\Book;

use App\Entity\Author;
use App\Entity\Book;
use App\Helpers\PhpSpreadsheetHelper;
use App\Repository\Book\BookRepositoryInterface;
use App\Services\Author\AuthorService;
use App\Services\Publisher\PublisherService;
use App\Traits\ApiResponse;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BookService
{
    use ApiResponse;

    /**
     * @var BookRepositoryInterface
     */
    private $bookRepository;

    /**
     * @var AuthorService
     */
    private $authorService;

    /**
     * @var PublisherService
     */
    private $publisherService;

    public function __construct(
        BookRepositoryInterface $bookRepository
    ){
        $this->bookRepository = $bookRepository;
    }

    /**
     * Returns all Books.
     *
     * @return array
     */
    public function all(): array {
        return $this->bookRepository->all();
    }

    /**
     * Returns all Books with custom query.
     *
     * @return array
     */
    public function getCustomQueryData(): array {
        return $this->bookRepository->getCustomQueryData();
    }

    public function queryTransactions(): ?Book {
        return $this->bookRepository->queryTransactions();
    }

    /**
     * Gets a book by id.
     *
     * @param int $id
     * @return Book|null
     */
    public function findOrFail(int $id): ?Book{
        return $this->bookRepository->findOrFail($id);
    }

    /**
     * Filters and pagination for books list.
     *
     * @param Request $request
     * @return PaginationInterface|array
     */
    public function filters(Request $request){
        return $this->bookRepository->filters($request);
    }

    /**
     * Get books by author.
     *
     * @param Author $author
     * @return array
     */
    public function findByAuthor(Author $author): array {
        return $this->bookRepository->findByAuthor($author);
    }

    /**
     * Create a book.
     *
     * @param array $params
     * @return Book
     */
    public function create(array $params): Book {
        return $this->bookRepository->create($params);
    }

    /**
     * Export to excel.
     *
     * @param array $data
     * @return BinaryFileResponse
     * @throws PhpSpreadsheetException
     */
    public function export(array $data): BinaryFileResponse {
        $data = $this->formatDataToExport($data);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Book List');
        $sheet->fromArray($data,null);

        PhpSpreadsheetHelper::setAutoSizeColumns($sheet);

        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'book_list.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($tempFile);

        // Return the excel file as an attachment
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * Get data to export.
     *
     * @param array $books
     * @return array
     */
    private function formatDataToExport(array $books): array {

        $data = [['Title', 'Summary', 'Price']];

        foreach ($books as $book){
            $data[] = [
                $book->getTitle(),
                $book->getSummary(),
                $book->getPrice()
            ];
        }

        return $data;
    }
}