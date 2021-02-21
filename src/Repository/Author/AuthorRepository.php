<?php

namespace App\Repository\Author;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use App\Entity\Author;
use App\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuthorRepository extends BaseEntityRepository implements AuthorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    protected function getEntityClass(): string
    {
        return Author::class;
    }

    public function all(): array {
        $qb = $this->createQueryBuilder('a');

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets an author by id.
     *
     * @param int $id
     * @return Author|null
     * @throws Exception
     */
    public function findOrFail(int $id): ?Author {
        if(!$entity = $this->find($id)){
            throw new Exception("No se encontró el author con el id especificado.");
        }

        return $entity;
    }

    /**
     * Create an Author.
     *
     * @param array $params
     * @return Author
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(array $params): Author {
        $entity = new Author();

        $entity->setName($params['name']);
        $entity->setActive(1);
        $entity->setCreatedAt(new DateTime('now'));

        $this->saveEntity($entity);

        return $entity;
    }
}