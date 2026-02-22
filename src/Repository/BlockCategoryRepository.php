<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symkit\BuilderBundle\Entity\BlockCategory;

/**
 * @extends ServiceEntityRepository<BlockCategory>
 */
class BlockCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockCategory::class);
    }
}
