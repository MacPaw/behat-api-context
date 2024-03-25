<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context\Api;

use BehatApiContext\Service\ResetManager\ResetManagerInterface;
use ReflectionClass;
use ReflectionException;

final class InitApiContextsTest extends AbstractApiContextTest
{
    /**
     * @throws ReflectionException
     */
    public function testInitBefore(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $savedValuesProp = $reflectionClass->getProperty('savedValues');
        $savedValuesProp->setAccessible(true);
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);
        $serverParamsProp = $reflectionClass->getProperty('serverParams');
        $serverParamsProp->setAccessible(true);
        $requestParamsProp = $reflectionClass->getProperty('requestParams');
        $requestParamsProp->setAccessible(true);

        $savedValuesProp->setValue($this->apiContext, ['key' => 'value']);
        $headersProp->setValue($this->apiContext, ['key' => 'value']);
        $serverParamsProp->setValue($this->apiContext, ['key' => 'value']);
        $requestParamsProp->setValue($this->apiContext, ['key' => 'value']);

        $this->assertNotEmpty($savedValuesProp->getValue($this->apiContext));
        $this->assertNotEmpty($headersProp->getValue($this->apiContext));
        $this->assertNotEmpty($serverParamsProp->getValue($this->apiContext));
        $this->assertNotEmpty($requestParamsProp->getValue($this->apiContext));

        $this->apiContext->beforeScenario();
        $this->assertEmpty($savedValuesProp->getValue($this->apiContext));
        $this->assertEmpty($headersProp->getValue($this->apiContext));
        $this->assertEmpty($serverParamsProp->getValue($this->apiContext));
        $this->assertEmpty($requestParamsProp->getValue($this->apiContext));
    }

    public function testAddResetManager(): void
    {
        /** @var ResetManagerInterface $resetManager */
        $resetManager = $this->createMock(ResetManagerInterface::class);

        $reflectionClass = new ReflectionClass($this->apiContext);
        $resetManagersProp = $reflectionClass->getProperty('resetManagers');
        $resetManagersProp->setAccessible(true);

        $this->assertEmpty($resetManagersProp->getValue($this->apiContext));

        $this->apiContext->addKernelResetManager($resetManager);

        $this->assertNotEmpty($resetManagersProp->getValue($this->apiContext));
    }
}
