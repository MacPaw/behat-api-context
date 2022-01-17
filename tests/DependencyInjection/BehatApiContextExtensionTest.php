<?php

declare(strict_types=1);

namespace DependencyInjection;

use BehatApiContext\Context\ApiContext;
use BehatApiContext\DependencyInjection\BehatApiContextExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class BehatApiContextExtensionTest extends TestCase
{
    public function testHasServices(): void
    {
        $extension = new BehatApiContextExtension();
        $container = new ContainerBuilder();

        $this->assertInstanceOf(Extension::class, $extension);

        $extension->load([], $container);

        $this->assertTrue($container->has(ApiContext::class));
    }
}
