<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Twig;

use Symkit\MetadataBundle\JsonLd\Renderer\JsonLdRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class JsonLdTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly JsonLdRenderer $jsonLdRenderer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_json_ld', $this->renderJsonLd(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderJsonLd(): string
    {
        return $this->jsonLdRenderer->render();
    }
}
