<?php


namespace App\Services\Publisher;

use App\Entity\Publisher;
use App\Repository\Publisher\PublisherRepositoryInterface;

class PublisherService
{
    /**
     * @var PublisherRepositoryInterface
     */
    private $publisherRepository;

    public function __construct(PublisherRepositoryInterface $publisherRepository)
    {
        $this->publisherRepository = $publisherRepository;
    }

    /**
     * Gets a publisher by id.
     *
     * @param int $id
     * @return Publisher|null
     */
    public function findOrFail(int $id): ?Publisher {
        return $this->publisherRepository->findOrFail($id);
    }

    /**
     * Create an Publisher.
     *
     * @param array $params
     * @return Publisher
     */
    public function create(array $params): Publisher {
        return $this->publisherRepository->create($params);
    }
}