<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use BehatApiContext\Context\ApiContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractApiContextTest extends TestCase
{
    protected ApiContext $apiContext;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RequestStack $requestStackMock */
        $requestStackMock = $this->createMock(RequestStack::class);
        $this->apiContext = new ApiContext($this->configureRouter(), $requestStackMock, $this->getKernelMock());
    }

    protected function configureRouter(): RouterInterface
    {
        /** @var RouterInterface $routerMock */
        $routerMock = $this->createMock(RouterInterface::class);

        return $routerMock;
    }

    protected function getKernelMock(): KernelInterface
    {
        $kernel = $this->createMock(KernelInterface::class);
        assert($kernel instanceof KernelInterface);

        return $kernel;
    }
}
