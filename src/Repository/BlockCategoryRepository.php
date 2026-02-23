<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;

/**
 * @extends ServiceEntityRepository<BlockCategoryEntityInterface>
 */
final class BlockCategoryRepository extends ServiceEntityRepository
{
    /**
     * @param class-string<BlockCategoryEntityInterface> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }
}
