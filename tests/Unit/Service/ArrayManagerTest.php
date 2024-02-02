<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Service;

use BehatApiContext\Service\StringManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use PHPUnit\Framework\TestCase;

class ArrayManagerTest extends TestCase
{
    private const SUBSTITUTION_VALUES = 'substitutionValues';
    private const INITIAL_STRING = 'initialString';
    private const RESULT_STRING = 'resultString';

    private StringManager $stringManager;

    protected function setUp(): void
    {
        $this->stringManager = new StringManager();
    }

    #[Test]
    #[DataProvider('getSubstituteValuesDataProvider')]
    public function testSubstituteValuesSuccess(
        array $substitutionValues,
        string $initialString,
        string $resultString
    ): void {
        $result = $this->stringManager->substituteValues($substitutionValues, $initialString);
        self::assertSame($resultString, $result);
    }

    public static function getSubstituteValuesDataProvider(): array
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

    #[Test]
    public function testSubstituteValuesKeyNotFound(): void
    {
        $substitutionValues = ['headerKey' => 'headerValue'];
        $initialString = 'I send request {{paramKey}} param and {{headerKey}} header';

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Key not found");
        $this->stringManager->substituteValues($substitutionValues, $initialString);
    }

    #[Test]
    public function testSubstituteValuesInvalidSyntax(): void
    {
        $substitutionValues = ['headerKey' => 'headerValue'];
        $initialString = 'I send request {{headerKey header';

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Invalid syntax");
        $this->stringManager->substituteValues($substitutionValues, $initialString);
    }
}
