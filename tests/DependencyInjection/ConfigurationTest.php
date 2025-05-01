<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\DependencyInjection;

use BehatApiContext\DependencyInjection\Configuration;
use BehatApiContext\Service\ResetManager\DoctrineResetManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testProcessConfigurationWithEmptyConfiguration(): void
    {
        $expectedBundleDefaultConfig = [
            'kernel_reset_managers' => [],
            'use_orm_context' => true,
        ];

        $this->assertSame($expectedBundleDefaultConfig, $this->processConfiguration([]));
    }

    public function testProcessConfigurationWithDefaultConfiguration(): void
    {
        $config = [
            'behat_api_context' => [
                'kernel_reset_managers' => [],
                'use_orm_context' => true,
            ]
        ];

        $expectedBundleDefaultConfig = [
            'kernel_reset_managers' => [],
            'use_orm_context' => true,
        ];

        $this->assertSame($expectedBundleDefaultConfig, $this->processConfiguration($config));
    }

    public function testProcessConfigurationWithFilledConfiguration(): void
    {
        $config = [
            'behat_api_context' => [
                'kernel_reset_managers' => [
                    DoctrineResetManager::class
                ],
                'use_orm_context' => true,
            ]
        ];

        $expectedBundleDefaultConfig = [
            'kernel_reset_managers' => [
                DoctrineResetManager::class
            ],
            'use_orm_context' => true,
        ];

        $this->assertSame($expectedBundleDefaultConfig, $this->processConfiguration($config));
    }

    public function testProcessConfigurationWithoutDoctrineOrm(): void
    {
        $configuration = new class extends Configuration {
            public function getConfigTreeBuilder(): TreeBuilder
            {
                $treeBuilder = new TreeBuilder('behat_api_context');
                $root = $treeBuilder->getRootNode()->children();

                $root
                    ->arrayNode('kernel_reset_managers')
                        ->scalarPrototype()
                        ->end()
                    ->end()
                    ->booleanNode('use_orm_context')
                        ->defaultValue(false)
                        ->end()
                    ->end();

                return $treeBuilder;
            }
        };

        $expected = [
            'kernel_reset_managers' => [],
            'use_orm_context' => false,
        ];

        $this->assertSame($expected, $this->processConfiguration([], $configuration));
    }

    private function processConfiguration(array $configuration, ?Configuration $configObject = null): array
    {
        $processor = new Processor();
        $configurationObject = $configObject ?? new Configuration();

        return $processor->processConfiguration($configurationObject, $configuration);
    }
}
