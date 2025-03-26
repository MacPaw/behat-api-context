<?php

declare(strict_types=1);

namespace BehatApiContext\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use BehatApiContext\Service\ResetManager\ResetManagerInterface;
use BehatApiContext\Service\StringManager;
use RuntimeException;
use SimilarArrays\SimilarArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

class ApiContext implements Context
{
    private SimilarArray $similarArrayManager;
    private StringManager $stringManager;
    private RouterInterface $router;
    private RequestStack $requestStack;
    private ?Response $response;
    private KernelInterface $kernel;
    private array $resetManagers = [];

    /**
     * @var array<string,string> $headers
     */
    protected array $headers = [];

    /**
     * @var array<string,string> $serverParams
     */
    protected array $serverParams = [];

    /**
     * @var array<mixed> $requestParams
     */
    protected array $requestParams = [];

    /**
     * @var array<mixed> $savedValues
     */
    protected array $savedValues = [];

    public function __construct(
        RouterInterface $router,
        RequestStack $requestStack,
        KernelInterface $kernel
    ) {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->similarArrayManager = new SimilarArray();
        $this->stringManager = new StringManager();
    }

    public function addKernelResetManager(ResetManagerInterface $resetManager): void
    {
        $this->resetManagers[] = $resetManager;
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        $this->savedValues = [];
        $this->resetRequestOptions();
    }

    /**
     * @Given the :headerName request header contains :value
     */
    public function theRequestHeaderContains(string $header, string $value): void
    {
        $processedHeader = $this->stringManager->substituteValues(
            $this->savedValues,
            trim($value)
        );

        $this->headers[$header] = $processedHeader;
    }

    /**
     * @Given the :headerName request header contains multiline value:
     */
    public function theRequestHeaderContainsMultiline(string $header, PyStringNode $params): void
    {
        $processedParams = $this->stringManager->substituteValues($this->savedValues, trim($params->getRaw()));

        $this->headers[$header] = $processedParams;
    }

    /**
     * @Given the request ip is :ip
     */
    public function theRequestIpIs(string $ip): void
    {
        $this->serverParams['REMOTE_ADDR'] = $ip;
    }

    /**
     * @Given the request contains params:
     */
    public function theRequestContainsParams(PyStringNode $params): void
    {
        $processedParams = $this->stringManager->substituteValues(
            $this->savedValues,
            trim($params->getRaw())
        );

        $newRequestParams = (array) json_decode($processedParams, true, 512, JSON_THROW_ON_ERROR);
        $newRequestParams = $this->convertRunnableCodeParams($newRequestParams);
        $this->requestParams = array_merge($this->requestParams, $newRequestParams);
        $this->savedValues = array_merge($this->savedValues, $newRequestParams);
    }

    /**
     * @When I send :method request to :route route
     */
    public function iSendRequestToRoute(
        string $method,
        string $route
    ): void {
        $routeParams = $this->popRouteAttributesFromRequestParams($route, $this->requestParams);
        $postFields = [];
        $queryString = '';

        $url = $this->router->generate($route, $routeParams);
        $url = preg_replace('|^/app[^\.]*\.php|', '', $url);

        if (Request::METHOD_GET === $method) {
            $queryString = http_build_query($this->requestParams);
        }

        if (in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH, Request::METHOD_PUT, Request::METHOD_DELETE], true)) {
            $postFields = $this->requestParams;
        }

        $request = Request::create($url . '?' . $queryString, $method, $postFields);
        $request->headers->add($this->headers);
        $request->server->add($this->serverParams);

        $this->response = $this->handleRequestWithKernel($request);

        $this->resetRequestOptions();
    }

    private function handleRequestWithKernel(Request $request): Response
    {
        $request->headers->add($this->headers);
        $request->server->add($this->serverParams);

        $response = $this->kernel->handle($request);

        $this->requestStack->pop();
        $this->kernel->terminate($request, $response);

        foreach ($this->resetManagers as $resetManager) {
            if ($resetManager->needsReset($request->getMethod())) {
                $resetManager->reset($this->kernel);
            }
        }

        return $response;
    }

    /**
     * @param array<string,string> $requestParams
     *
     * @return array<string,string>
     */
    private function popRouteAttributesFromRequestParams(string $route, array &$requestParams): array
    {
        $routeParams = [];

        if (is_array($requestParams) && ($routeDecl = $this->router->getRouteCollection()->get($route))) {
            $requirements = $routeDecl->getRequirements();

            foreach ($requirements as $attribute => $requirement) {
                if (isset($requestParams[$attribute]) && strpos($attribute, '_') !== 0) {
                    $routeParams[$attribute] = $requestParams[$attribute];
                    unset($requestParams[$attribute]);
                }
            }
        }

        return $routeParams;
    }

    /**
     * @Then response status code should be :httpStatus
     */
    public function responseStatusCodeShouldBe(string $httpStatus): void
    {
        $response = $this->getResponse();

        if ((string) $response->getStatusCode() !== $httpStatus) {
            $message = sprintf(
                'HTTP code does not match %s (actual: %s). Response: %s',
                $httpStatus,
                $response->getStatusCode(),
                $response->sendContent()
            );

            throw new RuntimeException($message);
        }
    }

    /**
     * @Then response is JSON
     */
    public function responseIsJson(): void
    {
        $response = $this->getResponse();
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data)) {
            throw new RuntimeException("Response was not JSON\n" . $response->getContent());
        }
    }

    /**
     * @Then response should be empty
     */
    public function responseEmpty(): void
    {
        if (!empty($this->getResponse()->getContent())) {
            throw new RuntimeException('Content not empty');
        }
    }

    /**
     * @param PyStringNode $string
     *
     * @Then response should be JSON:
     */
    public function responseShouldBeJson(PyStringNode $string): void
    {
        $expectedResponse = json_decode(trim($string->getRaw()), true, 512, JSON_THROW_ON_ERROR);
        $actualResponse = json_decode($this->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if ($expectedResponse !== $actualResponse) {
            $prettyJSON = json_encode($actualResponse, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
            $message = sprintf("Expected JSON does not match actual JSON:\n%s\n", $prettyJSON);

            throw new RuntimeException($message);
        }
    }

    /**
     * @When I save :paramPath param from json response as :valueKey
     */
    public function iGetParamFromJsonResponse(string $paramPath, string $valueKey): void
    {
        $actualResponse = json_decode($this->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $pathKeys = explode('.', $paramPath);

        foreach ($pathKeys as $key) {
            if (!isset($actualResponse[$key])) {
                throw new RuntimeException(sprintf('Response does not contain param "%s"', $paramPath));
            }

            $actualResponse = $actualResponse[$key];
        }

        $this->savedValues[$valueKey] = $actualResponse;
    }

    /**
     * phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     * @Then response should be JSON with variable fields :variableFields:
     * phpcs:enable
     */
    public function responseShouldBeJsonWithVariableFields(string $variableFields, PyStringNode $string): void
    {
        $this->compareStructureResponse($variableFields, $string, $this->getResponse()->getContent());
    }

    protected function compareStructureResponse(string $variableFields, PyStringNode $string, string $actualJSON): void
    {
        if ($actualJSON === '') {
            throw new RuntimeException('Response is not JSON');
        }

        $expectedResponse = (array) json_decode(trim($string->getRaw()), true);
        $actualResponse = (array) json_decode($actualJSON, true);
        $variableFields = $variableFields ? array_map('trim', explode(',', $variableFields)) : [];

        if (!$this->similarArrayManager->isArraysSimilar($expectedResponse, $actualResponse, $variableFields)) {
            $prettyJSON = json_encode($actualResponse, JSON_PRETTY_PRINT);
            $message = sprintf(
                "Expected JSON is not similar to the actual JSON with variable fields:\n%s\n",
                $prettyJSON
            );

            throw new RuntimeException($message);
        }
    }

    /**
     * @Then the :headerName response headers contains :headerValue
     */
    public function theResponseHeadersContains(string $headerName, string $headerValue): void
    {
        $this->checkResponseHeader($headerName, $headerValue);
    }

    /**
     * @And the :headerName response headers contains :headerValue
     */
    public function theAndResponseHeadersContains(string $headerName, string $headerValue): void
    {
        $this->checkResponseHeader($headerName, $headerValue);
    }

    protected function checkResponseHeader(string $headerName, string $headerValue): void
    {
        $givenHeaderName = $this->stringManager->substituteValues(
            $this->savedValues,
            trim($headerName),
        );
        $givenHeaderValue = $this->stringManager->substituteValues(
            $this->savedValues,
            trim($headerValue),
        );

        $response = $this->getResponse();

        if (!$response->headers->has($givenHeaderName)) {
            $message = sprintf(
                'Response header %s does not exists',
                $givenHeaderName,
            );

            throw new RuntimeException($message);
        }

        $responseHeaderValue = $response->headers->get($givenHeaderName);

        if (null === $responseHeaderValue || !substr_count($responseHeaderValue, $givenHeaderValue) > 0) {
            $message = sprintf(
                'Response header %s does not match. Expected: %s, given value: %s',
                $givenHeaderName,
                $givenHeaderValue,
                $responseHeaderValue,
            );

            throw new RuntimeException($message);
        }
    }

    protected function convertRunnableCodeParams(array $requestParams): array
    {
        foreach ($requestParams as $key => $value) {
            if (is_array($value)) {
                $requestParams[$key] = $this->convertRunnableCodeParams($value);
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            $pregMatchValue = preg_match('/^<.*>$/', trim($value));

            if ($pregMatchValue === 0 || $pregMatchValue === false) {
                continue;
            }

            $command = substr(trim($value), 1, -1);

            try {
                // phpcs:disable
                $resultValue = eval('return ' . $command . ';');
                // phpcs:enable
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    sprintf(
                        'Failed run your code %s, error message: %s',
                        $value,
                        $exception->getMessage()
                    )
                );
            }

            if (is_null($resultValue)) {
                throw new \RuntimeException(
                    sprintf(
                        'Running code: %s - should not return the null',
                        $command
                    )
                );
            }

            $requestParams[$key] = $resultValue;
        }

        return $requestParams;
    }

    private function resetRequestOptions(): void
    {
        $this->headers = [];
        $this->serverParams = [];
        $this->requestParams = [];
    }

    protected function getResponse(): Response
    {
        if ($this->response === null) {
            throw new RuntimeException('Response is null.');
        }

        return $this->response;
    }

    public function geRequestParams(): array
    {
        return $this->requestParams;
    }
}
