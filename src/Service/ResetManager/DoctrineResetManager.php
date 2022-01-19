<?php

declare(strict_types=1);

namespace BehatApiContext\Service\ResetManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class DoctrineResetManager implements ResetManagerInterface
{
    public function needsReset(string $httpMethod): bool
    {
        return strtoupper($httpMethod) !== Request::METHOD_GET;
    }

    public function reset(KernelInterface $kernel): void
    {
        $container = $kernel->getContainer();

        if ($container->hasParameter('doctrine.entity_managers')) {
            /** @var array $entityManagers */
            $entityManagers = $container->getParameter('doctrine.entity_managers');

            foreach ($entityManagers as $entityManagerId) {
                if ($container->initialized($entityManagerId)) {
                    $em = $container->get($entityManagerId);
                    $em->clear();

                    $connection = $em->getConnection();

                    if ($connection->isConnected()) {
                        $connection->close();
                    }
                }
            }
        }
    }
}
