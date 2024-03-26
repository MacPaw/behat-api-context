<?php

declare(strict_types=1);

namespace BehatApiContext\DependencyInjection;

use BehatApiContext\Context\ApiContext;
use BehatApiContext\Context\ORMContext;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
        $this->enableOrmContext($config, $container);
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

    private function enableOrmContext(array $config, ContainerBuilder $container): void
    {
        $config['use_orm_context'] = $config['use_orm_context'] ?? true;

        if (!$config['use_orm_context']) {
            return;
        }

        if (!$container->has(EntityManagerInterface::class)) {
            throw new RuntimeException('Entity manager does not exists');
        }

        $entityManagerDef = $container->get(EntityManagerInterface::class);

        $ormContextDef = new Definition(ORMContext::class);
        $ormContextDef->setArgument('$manager', $entityManagerDef);
        $container->setDefinition(ORMContext::class, $ormContextDef);
    }
}
