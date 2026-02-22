<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symkit\MenuBundle\Entity\MenuItem;
use Symkit\MenuBundle\Mapper\MenuItemMapperInterface;
use Symkit\MenuBundle\Model\MenuLink;
use Symkit\MenuBundle\Service\MenuModelFactory;

final class MenuModelFactoryTest extends TestCase
{
    public function testCreateModelReturnsNullWhenNoMapperSupports(): void
    {
        $mapper = $this->createMock(MenuItemMapperInterface::class);
        $mapper->method('supports')->willReturn(false);

        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_LINK);

        $factory = new MenuModelFactory([$mapper]);

        self::assertNull($factory->createModel($entity));
    }

    public function testCreateModelUsesFirstSupportingMapper(): void
    {
        $link = new MenuLink('id', 'Label', '/url', null);
        $mapper = $this->createMock(MenuItemMapperInterface::class);
        $mapper->method('supports')->willReturn(true);
        $mapper->method('map')->willReturn($link);

        $entity = new MenuItem();
        $entity->setType(MenuItem::TYPE_LINK);

        $factory = new MenuModelFactory([$mapper]);

        self::assertSame($link, $factory->createModel($entity));
    }
}
