<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\DependencyInjection;

use BehatApiContext\Context\ApiContext;
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

        $apiContextDefinition = $container->getDefinition(ApiContext::class);
        $doctrineResetManagerDefinition = $container->getDefinition(DoctrineResetManager::class);

        $methodCalls = $apiContextDefinition->getMethodCalls();
        $this->assertDefinitionMethodCall(
            $methodCalls[0],
            'addKernelResetManager',
            [$doctrineResetManagerDefinition]
        );

        self::assertCount(1, $apiContextDefinition->getMethodCalls());
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
