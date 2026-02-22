<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symkit\MenuBundle\Service\ActiveMenuRegistry;

final class ActiveMenuRegistryTest extends TestCase
{
    public function testSetAndGetActiveId(): void
    {
        $registry = new ActiveMenuRegistry();
        self::assertNull($registry->getActiveId('primary'));

        $registry->setActiveId('primary', 'dashboard');
        self::assertSame('dashboard', $registry->getActiveId('primary'));

        $registry->setActiveId('primary', 'settings');
        self::assertSame('settings', $registry->getActiveId('primary'));

        $registry->setActiveId('footer', 'contact');
        self::assertSame('settings', $registry->getActiveId('primary'));
        self::assertSame('contact', $registry->getActiveId('footer'));
    }

    public function testHandleMetadata(): void
    {
        $registry = new ActiveMenuRegistry();
        $registry->handleMetadata([
            ['menuName' => 'primary', 'itemId' => 'home'],
            ['menuName' => 'sidebar', 'itemId' => 'profile'],
        ]);

        self::assertSame('home', $registry->getActiveId('primary'));
        self::assertSame('profile', $registry->getActiveId('sidebar'));
    }
}
