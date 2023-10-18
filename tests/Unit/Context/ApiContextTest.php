<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context;

use BehatApiContext\Context\ApiContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class ApiContextTest extends TestCase
{
    private const PARAMS_VALUES = 'paramsValues';
    private const INITIAL_PARAM_VALUE = 'initialParamValue';
    private ApiContext $apiContext;

    protected function setUp(): void
    {
        $routerMock = $this->createMock(RouterInterface::class);
        $requestStackMock = $this->createMock(RequestStack::class);
        $kernelMock = $this->createMock(KernelInterface::class);

        $this->apiContext = new ApiContext($routerMock, $requestStackMock, $kernelMock);
    }

    /**
     * @param array $paramsValues
     * @param string $initialParamValue
     *
     * @dataProvider getConvertRunnableCodeParamsDataSuccess
     */
    public function testConvertRunnableCodeParamsSuccess(array $paramsValues, string $initialParamValue): void
    {
        $this->assertEquals($initialParamValue, $paramsValues['dateFrom']);
        $this->assertEquals($initialParamValue, $paramsValues['levelOne']['dateFrom']);
        $this->assertEquals($initialParamValue, $paramsValues['levelOne']['levelTwo']['dateFrom']);

        $resultParamsValues = $this->apiContext->convertRunnableCodeParams($paramsValues);

        $this->assertIsInt($resultParamsValues['dateFrom']);
        $this->assertIsInt($resultParamsValues['levelOne']['dateFrom']);
        $this->assertIsInt($resultParamsValues['levelOne']['levelTwo']['dateFrom']);
        $this->assertEquals(10, strlen((string)$resultParamsValues['dateFrom']));
        $this->assertEquals(10, strlen((string)$resultParamsValues['levelOne']['dateFrom']));
        $this->assertEquals(10, strlen((string)$resultParamsValues['levelOne']['levelTwo']['dateFrom']));
        $this->assertEquals($paramsValues['tripId'], $resultParamsValues['tripId']);
        $this->assertEquals($paramsValues['dateTo'], $resultParamsValues['dateTo']);
        $this->assertEquals($paramsValues['levelOne']['dateTo'], $resultParamsValues['levelOne']['dateTo']);
        $this->assertEquals(
            $paramsValues['levelOne']['levelTwo']['dateTo'],
            $resultParamsValues['levelOne']['levelTwo']['dateTo']
        );
    }

    /**
     * @param array $paramsValues
     *
     * @dataProvider getConvertRunnableCodeParamsDataError
     */
    public function testConvertRunnableCodeParamsError(array $paramsValues): void
    {
        $this->expectException(\Error::class);
        $this->apiContext->convertRunnableCodeParams($paramsValues);
    }

    public function getConvertRunnableCodeParamsDataSuccess(): array
    {
        return [
            [
                self::PARAMS_VALUES => [
                    'tripId' => '26e185b9-a233-470e-b2d4-2818908a075f',
                    'dateTo' => 1680361181,
                    'dateFrom' => '<(new DateTimeImmutable())->getTimestamp()>',
                    'levelOne' => [
                        'dateTo' => 1680343281,
                        'dateFrom' => '<(new DateTimeImmutable())->getTimestamp()>',
                        'levelTwo' => [
                            'dateTo' => 1680360351,
                            'dateFrom' => '<(new DateTimeImmutable())->getTimestamp()>',
                        ]
                    ],
                ],
                self::INITIAL_PARAM_VALUE => '<(new DateTimeImmutable())->getTimestamp()>',
            ],
        ];
    }

    public function getConvertRunnableCodeParamsDataError(): array
    {
        return [
            [
                self::PARAMS_VALUES => [
                    'tripId' => '26e185b9-a233-470e-b2d4-2818908a075f',
                    'dateTo' => 1680360081,
                    'dateFrom' => '<(new DateTimeImutable())->getTimestamp()>',
                ],
            ],
            [
                self::PARAMS_VALUES => [
                    'tripId' => '26e185b9-a233-470e-b2d4-2818908a075f',
                    'levelOne' => [
                        'dateTo' => 1680360081,
                        'dateFrom' => '<(DateTimeImmutable)->getTimestamp()>',
                    ],
                ],
            ],
            [
                self::PARAMS_VALUES => [
                    'tripId' => '26e185b9-a233-470e-b2d4-2818908a075f',
                    'levelOne' => [
                        'levelTwo' => [
                            'dateTo' => 1680360081,
                            'dateFrom' => '<(ne DateTimeImmutable())->getTimestamp()>',
                        ]
                    ],
                ],
            ],
            [
                self::PARAMS_VALUES => [
                    'tripId' => '26e185b9-a233-470e-b2d4-2818908a075f',
                    'dateTo' => 1680360081,
                    'dateFrom' => '<>',
                ],
            ],
        ];
    }
}
