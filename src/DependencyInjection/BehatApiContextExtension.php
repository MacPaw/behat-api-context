<?php

declare(strict_types=1);

namespace BehatApiContext\DependencyInjection;

use BehatApiContext\Context\ApiContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class BehatApiContextExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array<string, mixed> $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->loadApiContext($config, $loader, $container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadApiContext(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $this->safeLoad($loader, 'api_context.xml');

        $this->configureKernelResetManagers(
            $config,
            $container,
            ApiContext::class
        );
    }

    /**
     * @param array<string, mixed> $config
     * @param class-string $contextClass
     */
    private function configureKernelResetManagers(
        array $config,
        ContainerBuilder $container,
        string $contextClass
    ): void {
        if (empty($config['kernel_reset_managers'])) {
            return;
        }

        $contextDefinition = $container->findDefinition($contextClass);

        foreach ($config['kernel_reset_managers'] as $resetManager) {
            $resetManagerDefinition = $container->findDefinition($resetManager);

            $contextDefinition->addMethodCall('addKernelResetManager', [$resetManagerDefinition]);
        }
    }

    private function safeLoad(XmlFileLoader $loader, string $file): void
    {
        $loader->load($file);
    }
}
