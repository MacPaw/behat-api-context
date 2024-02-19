<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class ResponseHasHeaderTest extends AbstractApiContextTest
{
    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->response = new Response();
        $reflectionClass = new \ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $responseProp->setValue($this->apiContext, $this->response);
        $responseProp->setAccessible(false);
    }

    public function testResponseHeadersContainsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->apiContext->theResponseHeadersContains('Content-Type', 'application/json');
    }

    public function testExceptionWhenHeaderValuesDoesNotEquals(): void
    {
        $this->expectException(RuntimeException::class);
        $reflectionClass = new \ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $response = new Response('', 200, ['Content-Type' => 'application/xml']);
        $response->headers->set('X-Content-Type', 'application/xml');
        $responseProp->setValue($this->apiContext, $response);

        $this->expectException(RuntimeException::class);
        $this->apiContext->theResponseHeadersContains('X-Content-Type', 'application/json');
    }

    public function testWhenHeaderValuesDoesNotEqualsSuccess(): void
    {
        $this->expectException(RuntimeException::class);
        $reflectionClass = new \ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $response = new Response('', 200, ['Content-Type' => 'application/xml']);
        $response->headers->set('X-Content-Type', 'application/xml');
        $responseProp->setValue($this->apiContext, $response);

        $this->apiContext->theResponseHeadersContains('X-Content-Type', 'application/json');
    }
}
