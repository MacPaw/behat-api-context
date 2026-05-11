<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context\Api;

use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Pattern\Policy\RegexPatternPolicy;
use Behat\Behat\Definition\Pattern\Policy\TurnipPatternPolicy;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ApiContextStepPatternTest extends TestCase
{
    private PatternTransformer $patternTransformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patternTransformer = new PatternTransformer();
        $this->patternTransformer->registerPatternPolicy(new RegexPatternPolicy());
        $this->patternTransformer->registerPatternPolicy(new TurnipPatternPolicy());
    }

    public function testRequestHeaderStepMatchesContentTypeAndJsonMime(): void
    {
        $pattern = $this->givenPatternForMethod('theRequestHeaderContains');
        $step = 'the "Content-Type" request header contains "application/json"';
        $match = $this->matchStep($pattern, $step);
        $this->assertIsArray($match);
        $this->assertSame('Content-Type', $match['header']);
        $this->assertSame('application/json', $match['value']);
    }

    public function testRequestHeaderStepDoesNotMatchUnquotedJsonMime(): void
    {
        $pattern = $this->givenPatternForMethod('theRequestHeaderContains');
        $step = 'the Content-Type request header contains application/json';
        $this->assertFalse($this->matchStep($pattern, $step));
    }

    public function testResponseHeaderStepMatchesQuotedNames(): void
    {
        $pattern = $this->givenPatternForMethod('theResponseHeadersContains');
        $step = 'the "Content-Type" response headers contains "application/json; charset=UTF-8"';
        $match = $this->matchStep($pattern, $step);
        $this->assertIsArray($match);
        $this->assertSame('Content-Type', $match['headerName']);
        $this->assertSame('application/json; charset=UTF-8', $match['headerValue']);
    }

    /**
     * @return array<int|string, string>|false
     */
    private function matchStep(string $pattern, string $stepText): array|false
    {
        $regex = $this->patternTransformer->transformPatternToRegex($pattern);
        if (preg_match($regex, $stepText, $matches) !== 1) {
            return false;
        }

        return $matches;
    }

    private function givenPatternForMethod(string $methodName): string
    {
        $reflectionMethod = new ReflectionMethod(
            \BehatApiContext\Context\ApiContext::class,
            $methodName
        );
        $doc = (string) $reflectionMethod->getDocComment();
        if (preg_match('/@Given\\s+(.+)/i', $doc, $m) || preg_match('/@Then\\s+(.+)/i', $doc, $m)) {
            return trim($m[1]);
        }

        $this->fail('No @Given/@Then pattern in docblock for ' . $methodName);
    }
}
