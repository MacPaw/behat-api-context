<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use Behat\Gherkin\Node\PyStringNode;
use ReflectionClass;
use ReflectionException;

final class GivenApiContextsTest extends AbstractApiContextTest
{
    /**
     * @throws ReflectionException
     */
    public function testGivenHeader(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);

        $this->assertEmpty($headersProp->getValue($this->apiContext));

        $this->apiContext->theRequestHeaderContains('key', 'value');

        $this->assertNotEmpty($headersProp->getValue($this->apiContext));

        $headersProp->setAccessible(false);
    }

    /**
     * @throws ReflectionException
     */
    public function testGivenMultilineHeader(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $headersProp = $reflectionClass->getProperty('headers');
        $headersProp->setAccessible(true);

        $this->assertEmpty($headersProp->getValue($this->apiContext));

        $this->apiContext->theRequestHeaderContainsMultiline('key', new PyStringNode(['value', 'value'], 2));

        $this->assertNotEmpty($headersProp->getValue($this->apiContext));

        $headersProp->setAccessible(false);
    }

    public function testGivenIps(): void
    {
        $reflectionClass = new ReflectionClass($this->apiContext);
        $serverParamsProp = $reflectionClass->getProperty('serverParams');
        $serverParamsProp->setAccessible(true);

        $params = $serverParamsProp->getValue($this->apiContext);
        $this->assertIsArray($params);
        $this->assertArrayNotHasKey('REMOTE_ADDR', $params);

        $this->apiContext->theRequestIpIs('10.10.10.10');

        $params = $serverParamsProp->getValue($this->apiContext);
        $this->assertIsArray($params);
        $this->assertArrayHasKey('REMOTE_ADDR', $params);
        $this->assertEquals('10.10.10.10', $params['REMOTE_ADDR']);
    }
}
