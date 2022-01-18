<?php

declare(strict_types=1);

namespace BehatApiContext\Context;

use _PHPStan_76800bfb5\Nette\Neon\Exception;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use RuntimeException;
use SimilarArrays\SimilarArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class ApiContext implements Context
{
    private SimilarArray $similarArrayManager;
    private RouterInterface $router;
    private RequestStack $requestStack;
    private ?Response $response;
    private KernelInterface $kernel;

    /**
     * @var array<string,string> $headers
     */
    private array $headers = [];

    /**
     * @var array<string,string> $serverParams
     */
    private array $serverParams = [];

    /**
     * @var array<string,string> $requestParams
     */
    private array $requestParams = [];

    /**
     * @var array<string,string> $savedValues
     */
    private array $savedValues = [];

    public function __construct(
        RouterInterface $router,
        RequestStack $requestStack,
        KernelInterface $kernel
    ) {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->similarArrayManager = new SimilarArray();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        $this->headers = [];
        $this->serverParams = [];
        $this->savedValues = [];
    }

    /**
     * @Given the :headerName request header contains :value
     */
    public function theRequestHeaderContains(string $header, string $value): void
    {
        $this->headers[$header] = $value;
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
        $newRequestParams = json_decode(trim($params->getRaw()), true, 512, JSON_THROW_ON_ERROR);
        $this->requestParams = array_merge($this->requestParams, $newRequestParams);
    }

    /**
     * @Given the request param :paramName contains saved value :savedValueKey
     */
    public function theRequestParamContainsSavedValue(string $param, string $savedValueKey): void
    {
        $this->requestParams[$param] = $this->savedValues[$savedValueKey];
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
        } elseif (Request::METHOD_POST === $method || Request::METHOD_PATCH === $method) {
            $postFields = $this->requestParams;
        }

        $request = Request::create($url . '?' . $queryString, $method, $postFields);
        $request->headers->add($this->headers);
        $request->server->add($this->serverParams);

        $this->response = $this->handleRequestWithKernel($request);
    }

    private function handleRequestWithKernel(Request $request): Response
    {
        $request->headers->add($this->headers);
        $request->server->add($this->serverParams);

        $response = $this->kernel->handle($request);

        $this->requestStack->pop();
        $this->kernel->terminate($request, $response);

        if (strtoupper($request->getMethod()) !== Request::METHOD_GET) {
//            $this->kernel->resetBundles();
        }

        return $response;
//        return new Response($response->getContent(), $response->getStatusCode(), $response->headers->all());
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
        if ($this->response === null) {
            throw new Exception();
        }

        if ((string) $this->response->getStatusCode() !== $httpStatus) {
            $message = sprintf(
                'HTTP code does not match %s (actual: %s). Response: %s',
                $httpStatus,
                $this->response->getStatusCode(),
                $this->response->sendContent()
            );

            throw new RuntimeException($message);
        }
    }

    /**
     * @Then response is JSON
     */
    public function responseIsJson(): void
    {
        if ($this->response === null) {
            throw new Exception();
        }

        $data = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data)) {
            throw new RuntimeException("Response was not JSON\n" . $this->response->getContent());
        }
    }

    /**
     * @Then response should be empty
     */
    public function responseEmpty(): void
    {
        if ($this->response === null) {
            throw new Exception();
        }

        if (!empty($this->response->getContent())) {
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
        if ($this->response === null) {
            throw new Exception();
        }

        $expectedResponse = json_decode(trim($string->getRaw()), true, 512, JSON_THROW_ON_ERROR);
        $actualResponse = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);

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
        if ($this->response === null) {
            throw new Exception();
        }

        $actualResponse = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);
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
        if ($this->response === null) {
            throw new Exception();
        }

        $this->compareStructureResponse($variableFields, $string, $this->response->getContent());
    }

    private function compareStructureResponse(string $variableFields, PyStringNode $string, string $actualJSON): void
    {
        if ($actualJSON === '') {
            throw new RuntimeException('Response is not JSON');
        }

        $expectedResponse = json_decode(trim($string->getRaw()), true);
        $actualResponse = json_decode($actualJSON, true);
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
}
