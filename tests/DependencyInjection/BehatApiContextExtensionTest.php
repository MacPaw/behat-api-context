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
        $doctrineResetManagerDefinition = $container->getDefinition(DoctrineResetManager::class);

        $methodCalls = $apiContextDefinition->getMethodCalls();
        $this->assertDefinitionMethodCall($methodCalls[0], 'addKernelResetManager', [$doctrineResetManagerDefinition]);
    }

    private function createContainerFromFixture(string $fixtureFile): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->registerExtension(new BehatApiContextExtension());
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        $this->loadFixture($container, $fixtureFile);

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
        $this->assertSame($method, $methodCall[0]);
        $this->assertEquals($arguments, $methodCall[1]);
    }
}
