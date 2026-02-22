<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Listener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symkit\MetadataBundle\Attribute\Seo;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;

final readonly class SeoListener
{
    /** @use ResolveControllerAttributeTrait<Seo> */
    use ResolveControllerAttributeTrait;

    public function __construct(
        private PageContextBuilderInterface $builder,
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $attribute = $this->resolveAttribute($event, Seo::class);
        if (null === $attribute) {
            return;
        }

        if (null !== $attribute->title) {
            $this->builder->setTitle($attribute->title);
        }

        if (null !== $attribute->description) {
            $this->builder->setDescription($attribute->description);
        }

        if (null !== $attribute->ogImage) {
            $this->builder->setOgImage($attribute->ogImage);
        }

        $this->builder->setOgType($attribute->ogType);
        $this->builder->setTwitterCard($attribute->twitterCard);

        if (null !== $attribute->robots) {
            $this->builder->setRobots($attribute->robots);
        }

        if (null !== $attribute->author) {
            $this->builder->setAuthor($attribute->author);
        }

        if (null !== $attribute->canonicalUrl) {
            $this->builder->setCanonicalUrl($attribute->canonicalUrl);
        }

        if ([] !== $attribute->properties) {
            $this->builder->addProperties($attribute->properties);
        }
    }
}
