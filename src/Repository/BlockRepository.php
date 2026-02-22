<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symkit\BuilderBundle\Contract\BlockEntityInterface;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;

/**
 * @extends ServiceEntityRepository<BlockEntityInterface>
 */
final class BlockRepository extends ServiceEntityRepository implements BlockRepositoryInterface
{
    /**
     * @param class-string<BlockEntityInterface> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return BlockEntityInterface[]
     */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
