<?php

namespace PhpSolution\FunctionalTest\Tester;

use PhpSolution\FunctionalTest\Response\ResponseWrapper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiTester
 */
class ApiTester
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $expectedStatusCode = Response::HTTP_OK;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $requestHeaders;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $responseClass;

    /**
     * @param Client $client
     */
    public function __construct(Client $client, string $responseClass = ResponseWrapper::class)
    {
        $this->client = $client;
        $this->responseClass = $responseClass;
        $this->requestHeaders = [];
    }

    /**
     * @param int $expectedStatusCode
     *
     * @return self
     */
    public function setExpectedStatusCode(int $expectedStatusCode): ApiTester
    {
        $this->expectedStatusCode = $expectedStatusCode;

        return $this;
    }

    /**
     * @param array $files
     *
     * @return self
     */
    public function setFiles(array $files): ApiTester
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return self
     */
    protected function authorize(): ApiTester
    {
        return $this;
    }

    /**
     * @return self
     */
    protected function setRequestContentType(): ApiTester
    {
        $this->requestHeaders['CONTENT_TYPE'] = 'application/json';

        return $this;
    }

    /**
     * @return array
     */
    protected function getRequestParameters(): array
    {
        return Request::METHOD_GET === $this->method ? $this->data : [];
    }

    /**
     * @return string|null
     */
    protected function getRequestContent(): ?string
    {
        return Request::METHOD_GET === $this->method ? null : json_encode($this->data);
    }

    /**
     * @return ApiTester
     */
    protected function assertResponse(): ApiTester
    {
        Assert::assertEquals($this->expectedStatusCode, $this->response->getStatusCode(), $this->response->getContent());

        return $this;
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return ResponseWrapper
     */
    public function sendGet(string $path, array $data = []): ResponseWrapper
    {
        return $this->sendRequest(Request::METHOD_GET, $path, $data);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return ResponseWrapper
     */
    public function sendPost(string $path, array $data = []): ResponseWrapper
    {
        return $this->sendRequest(Request::METHOD_POST, $path, $data);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return ResponseWrapper
     */
    public function sendPut(string $path, array $data = []): ResponseWrapper
    {
        return $this->sendRequest(Request::METHOD_PUT, $path, $data);
    }

    /**
     * @param string $path
     *
     * @return ResponseWrapper
     */
    public function sendDelete(string $path): ResponseWrapper
    {
        return $this->sendRequest(Request::METHOD_DELETE, $path, []);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return ResponseWrapper
     */
    public function sendPatch(string $path, array $data = []): ResponseWrapper
    {
        return $this->sendRequest(Request::METHOD_PATCH, $path, $data);
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $data
     *
     * @return ResponseWrapper
     */
    public function sendRequest(string $method, string $path, array $data = []): ResponseWrapper
    {
        $this->method = $method;
        $this->path = $path;
        $this->data = $data;

        $this
            ->authorize()
            ->setRequestContentType();
        $this->client->request($method, $path, $this->getRequestParameters(), $this->files, $this->requestHeaders, $this->getRequestContent());

        $this->response = $this->client->getResponse();

        return new $this->responseClass($this->assertResponse()->response);
    }
}