<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symkit\MetadataBundle\Attribute\Seo;
use Symkit\MetadataBundle\Builder\PageContextBuilder;
use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Listener\SeoListener;

final class SeoListenerTest extends TestCase
{
    public function testSeoAttributeOnMethod(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Method Title', description: 'Method Desc', ogType: OgType::ARTICLE)]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        $context = $builder->getContext();
        self::assertSame('Method Title', $context->title);
        self::assertSame('Method Desc', $context->description);
        self::assertSame(OgType::ARTICLE, $context->ogType);
    }

    public function testSeoAttributeOnClass(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new #[Seo(title: 'Class Title')] class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame('Class Title', $builder->getContext()->title);
    }

    public function testNoAttributeDoesNothing(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame('', $builder->getContext()->title);
    }

    public function testSubRequestIsIgnored(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Should Not Apply')]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action'], false);
        $listener->onKernelController($event);

        self::assertSame('', $builder->getContext()->title);
    }

    public function testSeoWithProperties(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(properties: ['author' => 'John', 'og:locale' => 'fr_FR'])]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        $context = $builder->getContext();
        self::assertSame(['author' => 'John', 'og:locale' => 'fr_FR'], $context->properties);
    }

    public function testInvokableController(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Invokable')]
            public function __invoke(): void
            {
            }
        };

        $event = $this->createEvent($controller);
        $listener->onKernelController($event);

        self::assertSame('Invokable', $builder->getContext()->title);
    }

    public function testSeoWithRobots(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Private', robots: 'noindex, nofollow')]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame('noindex, nofollow', $builder->getContext()->robots);
    }

    public function testSeoWithAuthor(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Article', author: 'Jane Doe')]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame('Jane Doe', $builder->getContext()->author);
    }

    public function testSeoWithCanonicalUrl(): void
    {
        $builder = new PageContextBuilder();
        $listener = new SeoListener($builder);

        $controller = new class {
            #[Seo(title: 'Canonical', canonicalUrl: 'https://example.com/canonical')]
            public function action(): void
            {
            }
        };

        $event = $this->createEvent([$controller, 'action']);
        $listener->onKernelController($event);

        self::assertSame('https://example.com/canonical', $builder->getContext()->canonicalUrl);
    }

    /**
     * @param callable|array{object, string} $controller
     */
    private function createEvent(callable|array $controller, bool $mainRequest = true): ControllerEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ControllerEvent(
            $kernel,
            $controller,
            $request,
            $mainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
        );
    }
}
