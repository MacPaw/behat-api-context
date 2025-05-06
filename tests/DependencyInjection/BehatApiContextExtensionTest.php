<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\DependencyInjection;

use BehatApiContext\Context\ApiContext;
use BehatApiContext\Context\ORMContext;
use BehatApiContext\DependencyInjection\BehatApiContextExtension;
use BehatApiContext\Service\ResetManager\DoctrineResetManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class BehatApiContextExtensionTest extends TestCase
{
    public function testWithEmptyConfig(): void
    {
        $container = $this->createContainerFromFixture('empty_bundle_config');
        $apiContextDefinition = $container->getDefinition(ApiContext::class);
        self::assertCount(0, $apiContextDefinition->getMethodCalls());
    }

    public function testWithFilledConfig(): void
    {
        $container = $this->createContainerFromFixture('filled_bundle_config');
        self::assertFalse($container->hasDefinition(ORMContext::class));

        $apiContextDefinition = $container->getDefinition(ApiContext::class);
        self::assertCount(0, $apiContextDefinition->getMethodCalls());
    }

    public function testWithOrmContextEnabled(): void
    {
        $container = $this->createContainerFromFixture('with_orm_context');

        self::assertTrue($container->hasDefinition(ORMContext::class));

        $ormContextDefinition = $container->getDefinition(ORMContext::class);
        $doctrineResetManagerDefinition = $container->getDefinition(DoctrineResetManager::class);

        $methodCalls = $ormContextDefinition->getMethodCalls();
        $this->assertDefinitionMethodCall(
            $methodCalls[0],
            'addKernelResetManager',
            [$doctrineResetManagerDefinition]
        );
    }

    public function testWithOrmContextDisabled(): void
    {
        $container = $this->createContainerFromFixture('without_orm_context');
        self::assertFalse($container->hasDefinition(ORMContext::class));
    }

    public function testWithOrmContextButWithoutResetManagers(): void
    {
        $container = $this->createContainerFromFixture('with_orm_context_without_reset_managers');

        self::assertTrue($container->hasDefinition(ORMContext::class));

        $ormContextDefinition = $container->getDefinition(ORMContext::class);
        $methodCalls = $ormContextDefinition->getMethodCalls();

        self::assertCount(0, $methodCalls);
    }

    private function createContainerFromFixture(string $fixtureFile): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->registerExtension(new BehatApiContextExtension());
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        $this->loadFixture($container, $fixtureFile);
        $container->loadFromExtension('behat_api_context');
        $container->compile();

        return $container;
    }

    private function loadFixture(ContainerBuilder $container, string $fixtureFile): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load($fixtureFile . '.yaml');
    }

    private function assertDefinitionMethodCall(array $methodCall, string $method, array $arguments): void
    {
        self::assertSame($method, $methodCall[0]);
        self::assertEquals($arguments, $methodCall[1]);
    }
}
