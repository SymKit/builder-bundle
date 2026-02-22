<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMElement;
use DOMNode;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\MediaBundle\Entity\Media;
use Symkit\MediaBundle\Repository\MediaRepository;
use Symkit\MediaBundle\Service\MediaUrlGenerator;
use Twig\Environment;

final readonly class ImageBlockStrategy extends AbstractBlockStrategy
{
    public function __construct(
        BlockRegistry $blockRegistry,
        Environment $twig,
        private MediaRepository $mediaRepository,
        private MediaUrlGenerator $urlGenerator,
    ) {
        parent::__construct($blockRegistry, $twig);
    }

    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'image';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return 'img' === mb_strtolower($node->nodeName);
    }

    public function createFromNode(DOMNode $node): ?array
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        return [
            'type' => 'image',
            'data' => [
                'url' => $node->getAttribute('src'),
                'alt' => $node->getAttribute('alt'),
                'mediaId' => null, // We can't know the ID from the URL easily during import
            ],
        ];
    }

    public function prepareData(array $data): array
    {
        $mediaId = $data['mediaId'] ?? null;
        if (!$mediaId) {
            return $data;
        }

        $media = $this->mediaRepository->find($mediaId);
        if ($media instanceof Media) {
            $data['url'] = $this->urlGenerator->generateUrl($media);
            $data['alt'] = $media->getAltText();
        } elseif (empty($data['url'])) {
            // Media ID provided but not found, and no existing URL fallback.
            // Explicitly set URL to null to prevent broken images or 404s if 'url' was garbage.
            $data['url'] = null;
        }

        return $data;
    }
}
