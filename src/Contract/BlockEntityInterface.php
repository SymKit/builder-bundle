<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

/**
 * Contract for Block entity (or custom implementation) used by BlockRegistry and BlockSynchronizer.
 */
interface BlockEntityInterface
{
    public function getCode(): ?string;

    public function getLabel(): ?string;

    public function getCategory(): ?BlockCategoryEntityInterface;

    public function getIcon(): ?string;

    public function getTemplate(): ?string;

    public function getHtmlCode(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getDefaultData(): array;

    public function setCode(string $code): BlockEntityInterface;

    public function setLabel(string $label): BlockEntityInterface;

    public function setCategory(BlockCategoryEntityInterface $category): BlockEntityInterface;

    public function setIcon(string $icon): BlockEntityInterface;

    public function setTemplate(?string $template): BlockEntityInterface;

    public function setHtmlCode(?string $htmlCode): BlockEntityInterface;

    /**
     * @param array<string, mixed> $defaultData
     */
    public function setDefaultData(array $defaultData): BlockEntityInterface;

    public function setIsActive(bool $isActive): BlockEntityInterface;
}
