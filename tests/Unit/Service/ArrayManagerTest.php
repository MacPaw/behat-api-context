<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Service;

use BehatApiContext\Service\ArrayManager;
use RuntimeException;
use PHPUnit\Framework\TestCase;

class ArrayManagerTest extends TestCase
{
    private const SUBSTITUTION_VALUES = 'substitutionValues';
    private const INITIAL_STRING = 'initialString';
    private const RESULT_STRING = 'resultString';

    private ArrayManager $arrayManager;

    protected function setUp(): void
    {
        $this->arrayManager = new ArrayManager();
    }

    /**
     * @param array $substitutionValues
     * @param string $initialString
     * @param string $resultString
     *
     * @dataProvider getSubstituteValuesDataProvider
     */
    public function testSubstituteValuesInStringSuccess(
        array $substitutionValues,
        string $initialString,
        string $resultString
    ): void {
        $result = $this->arrayManager->substituteValuesInString($substitutionValues, $initialString);
        self::assertSame($resultString, $result);
    }

    private function getSubstituteValuesDataProvider(): array
    {
        return [
            [
                self::SUBSTITUTION_VALUES => [
                    'headerKey' => 'headerValue'
                ],
                self::INITIAL_STRING => 'I send request with {{headerKey}} header',
                self::RESULT_STRING => 'I send request with headerValue header'
            ],
            [
                self::SUBSTITUTION_VALUES => [
                    'headerKey' => 'headerValue',
                    'paramKey' => 'paramValue'
                ],
                self::INITIAL_STRING => 'I send request {{paramKey}} param and {{headerKey}} header',
                self::RESULT_STRING => 'I send request paramValue param and headerValue header'
            ],
            [
                self::SUBSTITUTION_VALUES => [
                    'headerKey' => 'headerValue'
                ],
                self::INITIAL_STRING => '{{headerKey}} header',
                self::RESULT_STRING => 'headerValue header'
            ],
            [
                self::SUBSTITUTION_VALUES => [
                    'headerKey' => 'headerValue',
                    'paramKey' => 'paramValue'
                ],
                self::INITIAL_STRING => '{{paramKey}} param and {{headerKey}}',
                self::RESULT_STRING => 'paramValue param and headerValue'
            ],
            [
                self::SUBSTITUTION_VALUES => [
                    'headerKey' => 'headerValue',
                    'paramKey' => 'paramValue'
                ],
                self::INITIAL_STRING => '{{paramKey}}{{headerKey}}',
                self::RESULT_STRING => 'paramValueheaderValue'
            ],
        ];
    }

    public function testSubstituteValuesInStringKeyNotFound(): void
    {
        $substitutionValues = ['headerKey' => 'headerValue'];
        $initialString = 'I send request {{paramKey}} param and {{headerKey}} header';

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Key not found");
        $this->arrayManager->substituteValuesInString($substitutionValues, $initialString);
    }

    public function testSubstituteValuesInStringInvalidSyntax(): void
    {
        $substitutionValues = ['headerKey' => 'headerValue'];
        $initialString = 'I send request {{headerKey header';

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Invalid syntax");
        $this->arrayManager->substituteValuesInString($substitutionValues, $initialString);
    }
}
