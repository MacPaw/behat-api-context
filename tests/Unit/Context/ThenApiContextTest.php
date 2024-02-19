<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use Behat\Gherkin\Node\PyStringNode;
use JsonException;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ThenApiContextTest extends AbstractApiContextTest
{
    public function testResponseStatusCodeShouldBe(): void
    {
        $response = new Response('', 200);

        $reflectionClass = new ReflectionClass($this->apiContext);
        $method = $reflectionClass->getProperty('response');
        $method->setAccessible(true);
        $method->setValue($this->apiContext, $response);

        $this->apiContext->responseStatusCodeShouldBe('200');

        $this->expectException(\RuntimeException::class);
        $this->apiContext->responseStatusCodeShouldBe('201');
    }

    public function testResponseIsJson(): void
    {
        $response = new Response(json_encode(['status' => 'OK']), 200);

        $reflectionClass = new ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $responseProp->setValue($this->apiContext, $response);
        $this->apiContext->responseIsJson();

        $response = new Response('', 200);
        $responseProp->setValue($this->apiContext, $response);
        $this->expectException(JsonException::class);
        $this->apiContext->responseIsJson();
    }

    public function testExceptionWhenResponseIsEmptyJson(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $response = new Response('{}', 200);
        $responseProp->setValue($this->apiContext, $response);

        $this->expectException(RuntimeException::class);
        $this->apiContext->responseIsJson();
    }

    public function testResponseIsEmpty(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $response = new Response('', 200);
        $responseProp->setValue($this->apiContext, $response);

        $this->apiContext->responseEmpty();

        $response = new Response('{}', 200);
        $responseProp->setValue($this->apiContext, $response);

        $this->expectException(RuntimeException::class);
        $this->apiContext->responseEmpty();
    }

    public function testResponseShouldBeJson(): void
    {
        $response = new Response(json_encode(['status' => 'OK']), 200);

        $reflectionClass = new ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $responseProp->setValue($this->apiContext, $response);

        $this->apiContext->responseShouldBeJson(new PyStringNode(['{"status": "OK"}'], 1));

        $response = new Response('{}', 200);
        $responseProp->setValue($this->apiContext, $response);
        $this->expectException(RuntimeException::class);
        $this->apiContext->responseShouldBeJson(new PyStringNode(['{"status": "OK"}'], 1));
    }

    public function testResponseShouldBeJsonWithVariableFields(): void
    {
        $response = new Response('{"message": "Hello, World!"}');
        $reflection = new \ReflectionClass($this->apiContext);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($this->apiContext, $response);

        $variableFields = 'message, world';
        $string = new PyStringNode(['{"message": "Hello, World!"}'], 0);

        try {
            $this->apiContext->responseShouldBeJsonWithVariableFields($variableFields, $string);
        } catch (Throwable $e) {
            $this->assertStringContainsString(
                'Expected JSON is not similar to the actual JSON with variable fields:',
                $e->getMessage(),
            );
            $this->assertInstanceOf(RuntimeException::class, $e);
        }

        $this->expectException(RuntimeException::class);
        $response = new Response('');
        $property->setValue($this->apiContext, $response);
        $this->apiContext->responseShouldBeJsonWithVariableFields($variableFields, $string);
    }
}
