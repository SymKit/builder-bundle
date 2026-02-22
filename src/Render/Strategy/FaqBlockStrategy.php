<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;
use Symkit\BuilderBundle\Render\BlockStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final readonly class FaqBlockStrategy implements BlockStrategyInterface
{
    public function __construct(
        #[Autowire(service: 'fragment.handler')]
        private FragmentHandler $handler,
    ) {
    }

    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'faq_block';
    }

    public function prepareData(array $data): array
    {
        return $data;
    }

    public function render(array $block): string
    {
        // Render the FaqController::show action
        // We use the 'code' from block data
        $code = $block['data']['faqCode'] ?? null;

        if (!$code) {
            return '';
        }

        return $this->handler->render(new ControllerReference(
            'Symkit\FaqBundle\Controller\FaqController::show',
            ['code' => $code]
        ));
    }

    public function supportsNode(DOMNode $node): bool
    {
        return false;
    }

    public function createFromNode(DOMNode $node): ?array
    {
        return null;
    }
}
