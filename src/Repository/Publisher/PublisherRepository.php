<?php

namespace App\Repository\Publisher;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use App\Entity\Publisher;
use App\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PublisherRepository extends BaseEntityRepository implements PublisherRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    protected function getEntityClass(): string
    {
        return Publisher::class;
    }

    /**
     * Gets a publisher by id.
     *
     * @param int $id
     * @return Publisher|null
     * @throws Exception
     */
    public function findOrFail(int $id): ?Publisher {
        if(!$entity = $this->find($id)){
            throw new Exception("No se encontró el publisher con el id especificado.");
        }

        return $entity;
    }

    /**
     * Create a Publisher.
     *
     * @param array $params
     * @return Publisher
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(array $params): Publisher {
        $entity = new Publisher();

        $entity->setName($params['name']);
        $entity->setActive(1);
        // $entity->setCreatedAt(new DateTime('now'));

        $this->saveEntity($entity);

        return $entity;
    }
}