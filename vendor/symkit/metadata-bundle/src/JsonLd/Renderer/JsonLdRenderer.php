<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Renderer;

use Symkit\MetadataBundle\JsonLd\Service\JsonLdService;

final readonly class JsonLdRenderer
{
    public function __construct(
        private JsonLdService $jsonLdService,
    ) {
    }

    public function render(): string
    {
        $json = $this->jsonLdService->generate();

        if ('' === $json) {
            return '';
        }

        return \sprintf('<script type="application/ld+json">%s</script>', $json);
    }
}
