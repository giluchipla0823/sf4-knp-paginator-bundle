<?php

namespace App\Repository\Book;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Exception;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Publisher;
use App\Repository\BaseEntityRepository;
use App\Libraries\KnpPaginatorSearch;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class BookRepository extends BaseEntityRepository implements BookRepositoryInterface
{
    /**
     * @var KnpPaginatorSearch
     */
    private $knpPaginatorSearch;

    /**
     * @var PaginatorInterface
     */
    private $paginator;
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(
        ManagerRegistry $registry,
        KnpPaginatorSearch $knpPaginatorSearch,
        PaginatorInterface $paginator
    ){
        parent::__construct($registry);

        $this->knpPaginatorSearch = $knpPaginatorSearch;
        $this->paginator = $paginator;
        $this->registry = $registry;
    }

    protected function getEntityClass(): string
    {
        return Book::class;
    }

    /**
     * Returns all books.
     *
     * @return array
     */
    public function all(): array {
        $qb = $this->createQueryBuilder('b');

        return $qb->getQuery()->getResult();
    }

    /**
     * Probando Transacciones.
     *
     * @return Book|null
     * @throws ConnectionException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function queryTransactions(): ?Book {
        $em = $this->getEntityManager();

        $connection = $em->getConnection();
        $connection->beginTransaction();

        $book = null;

        try {
            $author = new Author();

            $author->setName('Author test 4');
            $author->setActive(1);
            $author->setCreatedAt(new DateTime('now'));

            $em->persist($author);
            $em->flush();

            $publisher = new Publisher();
            $publisher->setName('Publisher test 4');
            $publisher->setActive(1);
            $publisher->setCreatedAt(new DateTime('now'));

            $em->persist($publisher);
            $em->flush();

            $book = $this->create([
                'title' => 'Book Gino 4',
                'summary' => 'Summary Gino 4',
                'description' => 'Description Gino 2',
                'quantity' => 10,
                'price' => 5,
                'author' => $author,
                'publisher' => $publisher,
            ]);

            $connection->commit();
        }catch (Exception $exc) {
            $connection->rollBack();

            throw $exc;
        }

        return $book;
    }

    /**
     * Returns all books with custom query.
     *
     * @return array
     */
    public function getCustomQueryData(): array {
        $qb = $this->createQueryBuilder('b')
                   ->select([
                       'b.id',
                       'b.title',
                       'b.summary',
                       'b.description',
                       "DATE_FORMAT(b.createdAt, '%Y-%m-%d') AS created_at"
                   ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Filters and pagination for books list.
     *
     * @param Request $request
     * @return PaginationInterface|array
     */
    public function filters(Request $request) {
        $alias = $this->getTableName();

        $qb = $this->createQueryBuilder($alias)
            ->select([$alias]);

        $this->knpPaginatorSearch->setAliasWithEntities([
            'books' => [
                'entity_class' => Book::class,
                'mapping_fields' => [
                    'title' => 'title'
                ]
            ],
            'authors' => [
                'belongs_to' => 'books.author',
                'entity_class' => Author::class,
                'mapping_fields' => [
                    'name' => 'name'
                ]
            ],
            'publishers' => [
                'belongs_to' => 'books.publisher',
                'entity_class' => Publisher::class,
                'mapping_fields' => [
                    'name' => 'name'
                ]
            ]
        ])->handle($qb, $request);

        $paginated = $request->query->getInt('paginated', KnpPaginatorSearch::PAGINATED_OFF);

        if($request->query->has('export')){
            $paginated = KnpPaginatorSearch::PAGINATED_OFF;
        }

        if($paginated === KnpPaginatorSearch::PAGINATED_ON){
            return $this->paginator->paginate(
                $qb,
                $request->query->getInt('page', KnpPaginatorSearch::DEFAULT_PAGE_NUMBER),
                $request->query->getInt('limit', KnpPaginatorSearch::ITEMS_PER_PAGE)
            );
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a book by id.
     *
     * @param int $id
     * @return Book|null
     * @throws Exception
     */
    public function findOrFail(int $id): ?Book {
        if(!$entity = $this->find($id)){
            throw new Exception("No se encontró el book con el id especificado.");
        }

        return $entity;
    }

    /**
     * Gets books by author.
     *
     * @param Author $author
     * @return array
     */
    public function findByAuthor(Author $author): array {
        return $this->findBy(['author' => $author]);
    }

    /**
     * Create a book.
     *
     * @param array $params
     * @return Book
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(array $params): Book {
        $entity = new Book();
        $entity->setTitle($params['title']);
        $entity->setSummary($params['summary']);
        $entity->setDescription($params['description']);
        $entity->setQuantity($params['quantity']);
        $entity->setPrice($params['price']);
        $entity->setAuthor($params['author']);
        $entity->setPublisher($params['publisher']);
        $entity->setCreatedAt(new DateTime('now'));

        $this->saveEntity($entity);

        return $entity;
    }
}