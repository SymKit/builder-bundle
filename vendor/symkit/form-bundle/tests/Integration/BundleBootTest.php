<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\FormBundle\FormBundle;

final class BundleBootTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(FormBundle::class);
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                'form' => ['enabled' => true],
                'csrf_protection' => false,
            ]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleBootsWithoutError(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        self::assertInstanceOf(ContainerInterface::class, $container);
    }
}
