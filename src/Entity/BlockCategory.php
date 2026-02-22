<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlockCategoryRepository::class)]
#[ORM\Table(name: 'builder_block_category')]
#[UniqueEntity(fields: ['code'], message: 'This category code already exists.', groups: ['create', 'edit'])]
class BlockCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(groups: ['create', 'edit'])]
    #[Assert\Regex(pattern: '/^[a-z0-9_]+$/', message: 'Code must be snake_case.', groups: ['create', 'edit'])]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(groups: ['create', 'edit'])]
    private ?string $label = null;

    #[ORM\Column]
    private int $position = 0;

    /**
     * @var Collection<int, Block>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Block::class)]
    private Collection $blocks;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }
}
