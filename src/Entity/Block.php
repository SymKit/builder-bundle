<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Contract\BlockEntityInterface;
use Symkit\BuilderBundle\Validator\Constraints\BlockContentSource;

#[ORM\Entity]
#[ORM\Table(name: 'builder_block')]
#[UniqueEntity(fields: ['code'], message: 'This block code already exists.', groups: ['create', 'edit'])]
#[BlockContentSource(groups: ['create', 'edit'])]
class Block implements BlockEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType (Doctrine sets id on persist) */
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'The block code is required.', groups: ['create', 'edit'])]
    #[Assert\Length(max: 100, groups: ['create', 'edit'])]
    #[Assert\Regex(
        pattern: '/^[a-z0-9_]+$/',
        message: 'The code must be in snake_case (lowercase letters, numbers, and underscores only).',
        groups: ['create', 'edit'],
    )]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'The display label is required.', groups: ['create', 'edit'])]
    #[Assert\Length(max: 100, groups: ['create', 'edit'])]
    private ?string $label = null;

    #[ORM\ManyToOne(targetEntity: BlockCategory::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'A category must be selected.', groups: ['create', 'edit'])]
    private ?BlockCategoryEntityInterface $category = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'An icon identifier is required.', groups: ['create', 'edit'])]
    #[Assert\Length(max: 100, groups: ['create', 'edit'])]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, groups: ['create', 'edit'])]
    private ?string $template = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $htmlCode = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $defaultData = [];

    #[ORM\Column]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCategory(): ?BlockCategoryEntityInterface
    {
        return $this->category;
    }

    public function setCategory(BlockCategoryEntityInterface $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getHtmlCode(): ?string
    {
        return $this->htmlCode;
    }

    public function setHtmlCode(?string $htmlCode): self
    {
        $this->htmlCode = $htmlCode;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getDefaultData(): array
    {
        return $this->defaultData;
    }

    /** @param array<string, mixed> $defaultData */
    public function setDefaultData(array $defaultData): self
    {
        $this->defaultData = $defaultData;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getLabel();
    }
}
