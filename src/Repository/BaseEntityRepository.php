<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;

abstract class BaseEntityRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getEntityClass());
    }

    abstract protected function getEntityClass(): string;

    /**
     * Store in memory.
     *
     * @param object $entity
     * @throws ORMException
     */
    public function persistEntity(object $entity): void {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws MappingException
     */
    public function flushData(): void {
        $em = $this->getEntityManager();

        $em->flush();
        $em->clear();
    }

    /**
     * Persist and apply changes in the database.
     *
     * @param object $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveEntity(object $entity): void{
        $em = $this->getEntityManager();

        $em->persist($entity);
        $em->flush();
    }

    /**
     * Delete record.
     *
     * @param object $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeEntity(object $entity): void{
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Get the name of an entity's table.
     *
     * @return string
     */
    protected function getTableName(): string{
        return $this->getClassMetadata()->getTableName();
    }
}