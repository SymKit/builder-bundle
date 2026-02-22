<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symkit\BuilderBundle\Entity\Block;

/**
 * @extends ServiceEntityRepository<Block>
 */
class BlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    /**
     * @return Block[]
     */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
