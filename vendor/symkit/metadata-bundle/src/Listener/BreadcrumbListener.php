<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Listener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;

final readonly class BreadcrumbListener
{
    /** @use ResolveControllerAttributeTrait<Breadcrumb> */
    use ResolveControllerAttributeTrait;

    public function __construct(
        private BreadcrumbServiceInterface $breadcrumbService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $attribute = $this->resolveAttribute($event, Breadcrumb::class);
        if (null === $attribute) {
            return;
        }

        $this->breadcrumbService->initialize($attribute->context);

        foreach ($attribute->items as $item) {
            $label = $item['label'];
            $url = $item['url'] ?? null;

            if (null === $url && isset($item['route'])) {
                $url = $this->urlGenerator->generate(
                    $item['route'],
                    $item['params'] ?? [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );
            }

            if (null !== $url) {
                $this->breadcrumbService->add($label, $url);
            }
        }
    }
}
