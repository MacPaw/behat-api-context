<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\DependencyInjection;

use BehatApiContext\DependencyInjection\Configuration;
use BehatApiContext\Service\ResetManager\DoctrineResetManager;
use PHPUnit\Framework\TestCase;
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

    private function processConfiguration(array $configuration): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configuration);
    }
}
