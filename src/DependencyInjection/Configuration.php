<?php

declare(strict_types=1);

namespace BehatApiContext\DependencyInjection;

use Composer\InstalledVersions;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('behat_api_context');
        $root = $treeBuilder->getRootNode()->children();

        $this->addKernelResetManagersSection($root);

        return $treeBuilder;
    }

    private function addKernelResetManagersSection(NodeBuilder $builder): void
    {
        $builder
            ->arrayNode('kernel_reset_managers')
                ->scalarPrototype()->end()
            ->end()

            ->booleanNode('use_orm_context')
                ->defaultValue($this->checkOrmContextDefValue())
            ->end()
        ->end();
    }

    private function checkOrmContextDefValue(): bool
    {
        return InstalledVersions::isInstalled('doctrine/orm');
    }
}
