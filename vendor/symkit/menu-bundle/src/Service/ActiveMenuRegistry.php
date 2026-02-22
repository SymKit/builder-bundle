<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Service;

final class ActiveMenuRegistry
{
    /** @var array<string, string> */
    private array $activeIds = [];

    public function setActiveId(string $menuCode, string $itemIdentifier): void
    {
        $this->activeIds[$menuCode] = $itemIdentifier;
    }

    public function getActiveId(string $menuCode): ?string
    {
        return $this->activeIds[$menuCode] ?? null;
    }

    /** @param array<array{menuName: string, itemId: string}> $metadata */
    public function handleMetadata(array $metadata): void
    {
        foreach ($metadata as $config) {
            $this->setActiveId($config['menuName'], $config['itemId']);
        }
    }
}
