<?php

declare(strict_types=1);

namespace BehatApiContext\DependencyInjection;

use BehatApiContext\Context\ORMContext;
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
        $this->loadOrmContext($config, $loader, $container);
    }

    /**
     * @param array<array> $config
     */
    private function loadApiContext(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $this->safeLoad($loader, 'api_context.xml');
    }

    /**
     * @param array<array> $config
     */
    private function loadOrmContext(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $this->safeLoad($loader, 'orm_context.xml');

        $useOrmContext = $config['use_orm_context'] ?? true;

        if (!$useOrmContext) {
            $container->removeDefinition(ORMContext::class);

            return;
        }

        if (isset($config['kernel_reset_managers'])) {
            $ormContextDefinition = $container->findDefinition(ORMContext::class);
            foreach ($config['kernel_reset_managers'] as $resetManager) {
                $resetManagerDefinition = $container->findDefinition($resetManager);

                $ormContextDefinition->addMethodCall(
                    'addKernelResetManager',
                    [$resetManagerDefinition],
                );
            }
        }
    }

    private function safeLoad(XmlFileLoader $loader, string $file): void
    {
        $loader->load($file);
    }
}
