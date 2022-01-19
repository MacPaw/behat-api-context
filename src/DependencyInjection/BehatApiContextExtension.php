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
    /**
     * @param array<array> $configs
     *
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->loadApiContext($config, $loader, $container);
    }

    /**
     * @param array<array> $config
     */
    private function loadApiContext(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $loader->load('api_context.xml');

        if (isset($config['kernel_reset_managers'])) {
            $apiContextDefinition = $container->findDefinition(ApiContext::class);
            foreach ($config['kernel_reset_managers'] as $resetManager) {
                $resetManagerDefinition = $container->findDefinition($resetManager);

                $apiContextDefinition->addMethodCall(
                    'addKernelResetManager',
                    [$resetManagerDefinition]
                );
            }
        }
    }
}
