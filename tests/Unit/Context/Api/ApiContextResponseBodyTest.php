<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context\Api;

use BehatApiContext\Context\ApiContext;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

final class ApiContextResponseBodyTest extends ApiContextTestCase
{
    public function testGetResponseBodyThrowsWhenResponseContentNotAvailable(): void
    {
        $response = $this->createMock(Response::class);
        $response->method('getContent')->willReturn(false);

        $method = new ReflectionMethod(ApiContext::class, 'getResponseBody');
        $method->setAccessible(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The response body is not available.');
        $method->invoke($this->apiContext, $response);
    }
}
