<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context\Api;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class WhenApiContextsTest extends ApiContextTestCase
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

        if ('testExceptionWhenRouteNotFound' === $this->name()) {
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

    public function testSendGetRequestToRoute(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params that should be converted to query string
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['page' => 1, 'limit' => 10]);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertNotNull($this->response);
        $this->assertEquals(Request::METHOD_GET, $this->request->getMethod());

        // Verify request params were reset
        $this->assertEmpty($requestParamsProp->getValue($this->apiContext));
    }

    public function testSendPostRequestToRouteWithFormData(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['name' => 'John', 'email' => 'john@example.com']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_POST, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_POST, $this->request->getMethod());

        // Verify request params were reset after the request
        $this->assertEmpty($requestParamsProp->getValue($this->apiContext));
    }

    public function testSendPostRequestToRouteWithJsonContent(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up headers to indicate JSON content
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($this->apiContext, ['Content-Type' => 'application/json']);

        // Set up request params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['name' => 'John', 'email' => 'john@example.com']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_POST, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_POST, $this->request->getMethod());

        // Verify headers were reset
        $this->assertEmpty($headersProp->getValue($this->apiContext));
    }

    public function testSendPatchRequestToRoute(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up headers to indicate JSON content
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($this->apiContext, ['Content-Type' => 'application/json']);

        // Set up request params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['name' => 'Jane']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_PATCH, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_PATCH, $this->request->getMethod());
    }

    public function testSendPutRequestToRoute(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['name' => 'John Updated']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_PUT, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_PUT, $this->request->getMethod());
    }

    public function testSendDeleteRequestToRoute(): void
    {
        $this->apiContext->iSendRequestToRoute(Request::METHOD_DELETE, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testSendRequestWithServerParams(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up server params
        $serverParamsProp = $reflectionClass->getProperty('serverParams');
        $serverParamsProp->setAccessible(true);
        $serverParamsProp->setValue($this->apiContext, ['REMOTE_ADDR' => '127.0.0.1']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);

        // Verify server params were reset
        $this->assertEmpty($serverParamsProp->getValue($this->apiContext));
    }

    public function testSendRequestWithRouteParameters(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params including route parameter
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['id' => '123', 'extra' => 'value']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertNotNull($this->response);
    }

    public function testSendGetRequestWithEmptyQueryString(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // No request params should result in empty query string
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, []);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_GET, $this->request->getMethod());
    }

    public function testSendPostRequestWithMixedCaseContentType(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up headers with mixed case Content-Type (should still detect JSON)
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($this->apiContext, ['Content-Type' => 'APPLICATION/JSON; charset=utf-8']);

        // Set up request params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['test' => 'value']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_POST, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_POST, $this->request->getMethod());
    }

    public function testSendPutRequestWithFormData(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params without JSON header (form data)
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['field' => 'value']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_PUT, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_PUT, $this->request->getMethod());
    }

    public function testSendPatchRequestWithFormData(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params without JSON header (form data)
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['status' => 'active']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_PATCH, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_PATCH, $this->request->getMethod());
    }

    public function testSendRequestWithMultipleHeaders(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up multiple headers
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($this->apiContext, [
            'Authorization' => 'Bearer token123',
            'Accept' => 'application/json',
            'X-Custom-Header' => 'custom-value'
        ]);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);

        // Verify headers were reset
        $this->assertEmpty($headersProp->getValue($this->apiContext));
    }

    public function testSendRequestWithComplexQueryParams(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up complex query params
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, [
            'filter' => 'active',
            'sort' => 'name',
            'page' => 2,
            'limit' => 50
        ]);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_GET, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertNotNull($this->response);
    }

    public function testSendDeleteRequestWithParams(): void
    {
        $reflectionClass = new \ReflectionClass($this->apiContext);

        // Set up request params (DELETE requests typically don't use body)
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);
        $requestParamsProp->setValue($this->apiContext, ['cascade' => 'true']);

        $this->apiContext->iSendRequestToRoute(Request::METHOD_DELETE, 'api_users_get');

        // Verify the request was made
        $this->assertNotNull($this->request);
        $this->assertEquals(Request::METHOD_DELETE, $this->request->getMethod());
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
            $router->expects($this->any())
                ->method('generate')
                ->willReturn('/api/users/1');
        }

        assert($router instanceof RouterInterface);

        return $router;
    }

    protected function getKernelMock(): KernelInterface&TerminableInterface
    {
        $kernel = $this->createMock(Kernel::class);

        assert($kernel instanceof MockObject);

        if (!$this->invalidRouteMock) {
            $kernel
                ->expects($this->any())
                ->method('handle')
                ->willReturn(new Response('{"status": "ok"}', 200));

            $kernel
                ->expects($this->any())
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
