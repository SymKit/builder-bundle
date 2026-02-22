<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<object>
 */
final class BlockRepository extends ServiceEntityRepository
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return object[]
     */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
