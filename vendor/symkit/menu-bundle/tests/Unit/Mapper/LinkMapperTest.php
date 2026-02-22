<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\Mapper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\MenuBundle\Entity\MenuItem;
use Symkit\MenuBundle\Mapper\LinkMapper;
use Symkit\MenuBundle\Model\MenuLink;
use Symkit\MenuBundle\Service\MenuModelFactory;

final class LinkMapperTest extends TestCase
{
    public function testSupportsReturnsTrueForTypeLink(): void
    {
        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_LINK);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $mapper = new LinkMapper($urlGenerator);

        self::assertTrue($mapper->supports($entity));
    }

    public function testSupportsReturnsFalseForOtherTypes(): void
    {
        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_DROPDOWN);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $mapper = new LinkMapper($urlGenerator);

        self::assertFalse($mapper->supports($entity));
    }

    public function testMapReturnsMenuLinkWithUrlAndIdentifier(): void
    {
        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_LINK);
        $entity->setLabel('Home');
        $entity->setUrl('/home');
        $entity->setIdentifier('home');

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $factory = new MenuModelFactory([]);
        $mapper = new LinkMapper($urlGenerator);

        $model = $mapper->map($entity, $factory);

        self::assertInstanceOf(MenuLink::class, $model);
        self::assertSame('home', $model->getId());
        self::assertSame('Home', $model->getLabel());
        self::assertSame('/home', $model->getUrl());
    }

    public function testMapUsesRouteWhenSet(): void
    {
        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_LINK);
        $entity->setLabel('About');
        $entity->setRoute('app_about');
        $entity->setIdentifier('about');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())->method('generate')->with('app_about', [])->willReturn('/about-us');

        $factory = new MenuModelFactory([]);
        $mapper = new LinkMapper($urlGenerator);

        $model = $mapper->map($entity, $factory);

        self::assertInstanceOf(MenuLink::class, $model);
        self::assertSame('/about-us', $model->getUrl());
    }
}
