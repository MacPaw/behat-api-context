<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use Behat\Gherkin\Node\PyStringNode;
use RuntimeException;

class ApiContextTest extends AbstractApiContextTest
{
    private const PARAMS_VALUES = 'paramsValues';
    private const INITIAL_PARAM_VALUE = 'initialParamValue';

    /**
     * @param PyStringNode $paramsValues
     * @param string $initialParamValue
     *
     * @dataProvider getTheRequestContainsParamsSuccess
     */
    public function testTheRequestContainsParamsSuccess(PyStringNode $paramsValues, string $initialParamValue): void
    {
        $this->assertTrue(str_contains($paramsValues->getStrings()[3], $initialParamValue));
        $this->assertTrue(str_contains($paramsValues->getStrings()[6], $initialParamValue));
        $this->assertTrue(str_contains($paramsValues->getStrings()[9], $initialParamValue));

        $this->apiContext->theRequestContainsParams($paramsValues);

        $this->assertIsInt($this->apiContext->geRequestParams()['dateFrom']);
        $this->assertIsInt($this->apiContext->geRequestParams()['levelOne']['dateFrom']);
        $this->assertIsInt($this->apiContext->geRequestParams()['levelOne']['levelTwo']['dateFrom']);

        $this->assertEquals(
            10,
            strlen((string)$this->apiContext->geRequestParams()['dateFrom']),
        );
        $this->assertEquals(
            10,
            strlen((string)$this->apiContext->geRequestParams()['levelOne']['dateFrom']),
        );
        $this->assertEquals(
            10,
            strlen((string)$this->apiContext->geRequestParams()['levelOne']['levelTwo']['dateFrom']),
        );

        $this->assertTrue(
            str_contains(
                $paramsValues->getStrings()[1],
                $this->apiContext->geRequestParams()['tripId'],
            ),
        );
        $this->assertTrue(
            str_contains(
                $paramsValues->getStrings()[2],
                strval($this->apiContext->geRequestParams()['dateTo']),
            ),
        );
        $this->assertTrue(
            str_contains(
                $paramsValues->getStrings()[5],
                strval($this->apiContext->geRequestParams()['levelOne']['dateTo']),
            ),
        );
        $this->assertTrue(
            str_contains(
                $paramsValues->getStrings()[8],
                strval($this->apiContext->geRequestParams()['levelOne']['levelTwo']['dateTo']),
            ),
        );
    }

    /**
     * @param PyStringNode $paramsValues
     *
     * @dataProvider getTheRequestContainsParamsRuntimeException
     */
    public function testTheRequestContainsParamsRuntimeException(PyStringNode $paramsValues): void
    {
        $this->expectException(RuntimeException::class);
        $this->apiContext->theRequestContainsParams($paramsValues);
    }

    public function getTheRequestContainsParamsSuccess(): array
    {
        return [
            [
                self::PARAMS_VALUES => new PyStringNode(
                    [
                        '{',
                        '    "tripId": "26e185b9-a233-470e-b2d4-2818908a075f",',
                        '    "dateTo": 1680361181,',
                        '    "dateFrom": "<(new DateTimeImmutable())->getTimestamp()>",',
                        '    "levelOne": {',
                        '      "dateTo": 1680343281,',
                        '      "dateFrom": "<(new DateTimeImmutable())->getTimestamp()>",',
                        '      "levelTwo": {',
                        '        "dateTo": 1680343281,',
                        '        "dateFrom": "<(new DateTimeImmutable())->getTimestamp()>"',
                        '      }',
                        '    }',
                        '}',
                    ],
                    12,
                ),
                self::INITIAL_PARAM_VALUE => '<(new DateTimeImmutable())->getTimestamp()>',
            ],
        ];
    }

    public function getTheRequestContainsParamsRuntimeException(): array
    {
        return [
            [
                self::PARAMS_VALUES => new PyStringNode(
                    [
                        '{',
                        '    "tripId": "26e185b9-a233-470e-b2d4-2818908a075f",',
                        '    "dateTo": 1680361181,',
                        '    "dateFrom": "<(new DateTimeImutable())->getTimestamp()>"',
                        '}',
                    ],
                    12,
                ),
            ],
            [
                self::PARAMS_VALUES => new PyStringNode(
                    [
                        '{',
                        '    "tripId": "26e185b9-a233-470e-b2d4-2818908a075f",',
                        '    "dateTo": 1680361181,',
                        '    "dateFrom": "<(DateTimeImmutable)->getTimestamp()>"',
                        '}',
                    ],
                    12,
                ),
            ],
            [
                self::PARAMS_VALUES => new PyStringNode(
                    [
                        '{',
                        '    "tripId": "26e185b9-a233-470e-b2d4-2818908a075f",',
                        '    "levelOne": {',
                        '      "levelTwo": {',
                        '        "dateTo": 1680343281,',
                        '        "dateFrom": "<(ne DateTimeImmutable())->getTimestamp()>"',
                        '      }',
                        '    }',
                        '}',
                    ],
                    12,
                ),
            ],
            [
                self::PARAMS_VALUES => new PyStringNode(
                    [
                        '{',
                        '    "tripId": "26e185b9-a233-470e-b2d4-2818908a075f",',
                        '    "dateTo": 1680361181,',
                        '    "dateFrom": "<>"',
                        '}',
                    ],
                    12,
                ),
            ],
        ];
    }
}
