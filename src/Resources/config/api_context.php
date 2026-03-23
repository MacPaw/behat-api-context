<?php

declare(strict_types=1);

use BehatApiContext\Context\ApiContext;
use BehatApiContext\Service\ResetManager\DoctrineResetManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ApiContext::class)
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->set(DoctrineResetManager::class)
        ->autowire(false)
        ->autoconfigure(false);
};
