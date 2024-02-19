<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use JsonException;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class WhenSendRequestToRouteApiContextsTest extends AbstractApiContextTest
{
    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testIGetParamFromJsonResponse(): void
    {
        $response = new Response((string) json_encode(['id' => 1]), 200);

        $reflectionClass = new \ReflectionClass($this->apiContext);
        $responseProp = $reflectionClass->getProperty('response');
        $responseProp->setAccessible(true);
        $responseProp->setValue($this->apiContext, $response);

        $this->apiContext->iGetParamFromJsonResponse('id', 'id_');
        $savedValuesProp = $reflectionClass->getProperty('savedValues');
        $savedValuesProp->setAccessible(true);
        $this->assertArrayHasKey('id_', $savedValuesProp->getValue($this->apiContext));

        $this->expectException(RuntimeException::class);
        $this->apiContext->iGetParamFromJsonResponse('not_existed', 'id_');
    }
}
