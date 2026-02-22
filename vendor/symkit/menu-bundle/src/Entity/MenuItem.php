<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symkit\MenuBundle\Contract\MenuItemEntityInterface;
use Symkit\MenuBundle\Repository\MenuItemRepository;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
#[Assert\Callback(callback: 'validateUrlOrRoute', groups: ['create', 'edit'])]
class MenuItem implements MenuItemEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // @phpstan-ignore property.unusedType (Doctrine assigns id on persist)

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?self $parent = null;

    /** @var Collection<int, MenuItem> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, groups: ['create', 'edit'])]
    private ?string $identifier = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['create', 'edit'])]
    #[Assert\Length(max: 255, groups: ['create', 'edit'])]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, groups: ['create', 'edit'])]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, groups: ['create', 'edit'])]
    private ?string $route = null;

    public const TYPE_LINK = 'link';

    public const TYPE_DROPDOWN = 'dropdown';

    public const TYPE_ADVANCED_DROPDOWN = 'advanced_dropdown';

    public const TYPE_MEGA = 'mega';

    public const TYPE_ADVANCED_ITEM = 'advanced_item';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(groups: ['create', 'edit'])]
    #[Assert\Length(max: 50, groups: ['create', 'edit'])]
    #[Assert\Choice(choices: [self::TYPE_LINK, self::TYPE_DROPDOWN, self::TYPE_ADVANCED_DROPDOWN, self::TYPE_MEGA, self::TYPE_ADVANCED_ITEM], groups: ['create', 'edit'])]
    private ?string $type = self::TYPE_LINK; // @phpstan-ignore property.unusedType (Doctrine can hydrate null)

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $badge = null;

    #[ORM\Column]
    private int $position = 0;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $options = [];

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
            $child->setMenu($this->getMenu()); // Ensure child belongs to same menu
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(?string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** @param array<string, mixed> $options */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function validateUrlOrRoute(ExecutionContextInterface $context): void
    {
        if ($this->url && $this->route) {
            $context->buildViolation('You cannot specify both a URL and a Symfony Route. Please choose one.')
                ->atPath('route')
                ->addViolation()
            ;
        }
    }

    public function __toString(): string
    {
        return $this->label ?? 'Menu Item';
    }
}
