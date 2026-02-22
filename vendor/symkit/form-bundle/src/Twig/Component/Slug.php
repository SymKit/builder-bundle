<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Twig\Component;

use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use RuntimeException;
use Stringable;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/** Service registration and live/template metadata are also configured in FormBundle::loadExtension(); AsLiveComponent is required by symfony/ux-live-component for Live detection. */
#[AsLiveComponent('Slug', template: '@SymkitForm/components/Slug.html.twig')]
final class Slug
{
    use DefaultActionTrait;

    private const MAX_UNIQUENESS_ATTEMPTS = 100;

    #[LiveProp(writable: true, onUpdated: 'onValueUpdated')]
    public ?string $value = null;

    #[LiveProp(writable: true, onUpdated: 'generateSlug')]
    public string $sourceValue = '';

    #[LiveProp(writable: true)]
    public bool $isLocked = true;

    #[LiveProp]
    public bool $unique = false;

    #[LiveProp]
    public ?string $entityClass = null;

    #[LiveProp]
    public string $slugField = 'slug';

    #[LiveProp]
    public mixed $entityId = null;

    #[LiveProp]
    public string $name = '';

    #[LiveProp]
    public ?string $repositoryMethod = null;

    #[LiveProp]
    public bool $autoUpdate = true;

    #[LiveProp]
    public ?string $targetId = null;

    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ?ManagerRegistry $doctrine = null,
    ) {
    }

    public function mount(?string $value = null): void
    {
        $this->value = $value;

        // If a value is already present (edit mode), disable auto-update
        // until the user explicitly re-locks the component.
        if ($value) {
            $this->autoUpdate = false;
        }
    }

    public function generateSlug(): void
    {
        if (!$this->isLocked || !$this->autoUpdate) {
            return;
        }

        // Don't overwrite if sourceValue is empty and we already have a value
        // (This prevents accidental resets if sync is lagging, although backend sync should fix it)
        if (empty($this->sourceValue) && !empty($this->value)) {
            return;
        }

        $slug = $this->slugger->slug((string) $this->sourceValue)->lower()->toString();

        if ($this->unique && $this->entityClass) {
            $slug = $this->ensureUniqueness($slug);
        }

        $this->value = $slug;
    }

    #[LiveAction]
    public function toggleLock(): void
    {
        $this->isLocked = !$this->isLocked;

        if ($this->isLocked) {
            $this->autoUpdate = true;
            $this->generateSlug();
        }
    }

    public function onValueUpdated(): void
    {
        if (!$this->value) {
            return;
        }

        // Slugify the manual input
        $slug = $this->slugger->slug($this->value)->lower()->toString();

        // Ensure uniqueness if enabled
        if ($this->unique && $this->entityClass) {
            $slug = $this->ensureUniqueness($slug);
        }

        $this->value = $slug;
    }

    private function ensureUniqueness(string $slug): string
    {
        if (null === $this->doctrine) {
            return $slug;
        }

        if (null === $this->entityClass) {
            return $slug;
        }

        /** @var class-string<object> $entityClass */
        $entityClass = $this->entityClass;

        $manager = $this->doctrine->getManagerForClass($entityClass);
        if (!$manager) {
            return $slug;
        }

        $repository = $manager->getRepository($entityClass);

        if ($this->repositoryMethod) {
            if (!method_exists($repository, $this->repositoryMethod)) {
                throw new InvalidArgumentException(\sprintf('Method "%s" not found in repository "%s".', $this->repositoryMethod, $repository::class));
            }

            /** @var string|int|float|bool|Stringable|null $result */
            $result = $repository->{$this->repositoryMethod}($slug, $this->entityId);

            return (string) $result;
        }

        if (!$repository instanceof \Doctrine\ORM\EntityRepository) {
            return $slug;
        }

        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            if ($counter > self::MAX_UNIQUENESS_ATTEMPTS) {
                throw new RuntimeException(\sprintf('Unable to generate a unique slug after %d attempts for "%s".', self::MAX_UNIQUENESS_ATTEMPTS, $originalSlug));
            }

            $qb = $repository->createQueryBuilder('e')
                ->select('count(e.id)')
                ->where(\sprintf('e.%s = :slug', $this->slugField))
                ->setParameter('slug', $slug)
            ;

            if ($this->entityId) {
                $qb->andWhere('e.id != :id')
                    ->setParameter('id', $this->entityId)
                ;
            }

            if (0 === $qb->getQuery()->getSingleScalarResult()) {
                break;
            }

            ++$counter;
            $slug = $originalSlug.'-'.$counter;
        }

        return $slug;
    }
}
