<?php

declare(strict_types=1);

namespace BehatApiContext\DependencyInjection;

use LogicException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('behat_api_context');
        $rootNode = $treeBuilder->getRootNode();
        if (!$rootNode instanceof ArrayNodeDefinition) {
            throw new LogicException('Expected configuration root to be an array node.');
        }

        $root = $rootNode->children();

        $this->addKernelResetManagersSection($root);

        return $treeBuilder;
    }

    private function addKernelResetManagersSection(NodeBuilder $builder): void
    {
        $kernelResetManagers = $builder->arrayNode('kernel_reset_managers');
        $kernelResetManagers->scalarPrototype()->end();
        $kernelResetManagers->end();
        $builder->end();
    }
}
