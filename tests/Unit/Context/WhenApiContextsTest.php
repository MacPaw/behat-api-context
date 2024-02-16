<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class WhenApiContextsTest extends AbstractApiContextTest
{
    private Route $route;
    private ?Request $request = null;
    private ?Response $response = null;
    private bool $invalidRouteMock = false;

    protected function setUp(): void
    {
        $this->route = new Route(
            '/api/users/{id}',
            ['_controller' => 'App\Controller\UserController::get'],
            ['id' => '\d+'],
        );

        if ('testExceptionWhenRouteNotFound' === $this->getName()) {
            $this->invalidRouteMock = true;
        }

        parent::setUp();
    }

    public function testExceptionWhenRouteNotFound(): void
    {
        $this->invalidRouteMock = true;
        $this->expectException(RouteNotFoundException::class);
        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, '/_api/users/{id}');
    }

    protected function configureRouter(): RouterInterface
    {
        $router = parent::configureRouter();
        assert($router instanceof MockObject);

        $routeCollection = new RouteCollection();
        $routeCollection->add('api_users_get', $this->route);

        $router->method('getRouteCollection')
            ->willReturn(
                $routeCollection,
            );

        if (true === $this->invalidRouteMock) {
            $router->expects($this->once())
                ->method('generate')
                ->willThrowException(new RouteNotFoundException());
        } else {
            $router->expects($this->once())
                ->method('generate')
                ->willReturn('/api/users/1');
        }

        assert($router instanceof RouterInterface);

        return $router;
    }

    protected function getKernelMock(): KernelInterface
    {
        $kernel = $this->createMock(Kernel::class);

        assert($kernel instanceof MockObject);

        if (!$this->invalidRouteMock) {
            $kernel
                ->expects($this->once())
                ->method('terminate')
                ->will(
                    $this->returnCallback(function (Request $request, Response $response): void {
                        $this->request = $request;
                        $this->response = $response;
                    }),
                );
        }

        assert($kernel instanceof KernelInterface);

        return $kernel;
    }
}
