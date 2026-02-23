<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\BuilderBundle\SymkitBuilderBundle;

final class BundleBootWithFeaturesTest extends KernelTestCase
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
        $kernel->addTestBundle(SymkitBuilderBundle::class);
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                'form' => ['enabled' => true],
                'csrf_protection' => false,
            ]);
            $container->loadFromExtension('symkit_builder', [
                'doctrine' => ['enabled' => false],
                'admin' => ['enabled' => false],
                'twig' => ['enabled' => true],
                'assets' => ['enabled' => false],
                'command' => ['enabled' => false],
                'live_component' => ['enabled' => false],
            ]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleBootsWithTwigEnabledWithoutError(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        self::assertInstanceOf(ContainerInterface::class, $container);
    }
}
