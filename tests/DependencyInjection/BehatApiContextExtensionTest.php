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

class BehatApiContextExtensionTest extends TestCase
{
    public function testWithEmptyConfig(): void
    {
        $container = $this->createContainerFromFixture('empty_bundle_config');

        $apiContextDefinition = $container->getDefinition(ApiContext::class);

        $methodCalls = $apiContextDefinition->getMethodCalls();
        self::assertCount(0, $methodCalls);
    }

    public function testWithFilledConfig(): void
    {
        $container = $this->createContainerFromFixture('filled_bundle_config');
        $apiContextDefinition = $container->getDefinition(ApiContext::class);

        $methodCalls = $apiContextDefinition->getMethodCalls();

        self::assertFalse($container->hasDefinition(ORMContext::class));
        self::assertCount(0, $methodCalls);
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

        $this->assertFalse($container->hasDefinition(ORMContext::class));

        $apiContextDefinition = $container->getDefinition(ApiContext::class);
        $methodCalls = $apiContextDefinition->getMethodCalls();

        $this->assertCount(0, $methodCalls);
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

    protected function loadFixture(ContainerBuilder $container, string $fixtureFile): void
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
