<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Facade functions for interact with Guzzle constraints.
 */
trait GuzzleAssertsTrait
{
    use AssertsTrait;

    /**
     * Asserts response match with the response schema.
     *
     * @param ResponseInterface $response
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertResponseMatch(
        ResponseInterface $response,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
    ) {
        $this->assertResponseMediaTypeMatch(
            $response->getHeader('Content-Type'),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        $httpCode = $response->getStatusCode();
        $headers = $this->inlineHeaders($response->getHeaders());

        $this->assertResponseHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );

        $this->assertResponseBodyMatch(
            json_decode($response->getBody()),
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );
    }

    /**
     * Asserts request match with the request schema.
     *
     * @param RequestInterface $request
     * @param SchemaManager $schemaManager
     * @param string $message
     */
    public function assertRequestMatch(
        RequestInterface $request,
        SchemaManager $schemaManager,
        $message = ''
    ) {
        $path = $request->getPath();
        $httpMethod = $request->getMethod();

        $headers = $this->inlineHeaders($request->getHeaders());

        $this->assertRequestHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        if (!empty((string) $request->getBody())) {
            $this->assertRequestMediaTypeMatch(
                $request->getHeader('Content-Type'),
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

        $this->assertRequestBodyMatch(
            json_decode($request->getBody()),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );
    }

    /**
     * Asserts response match with the response schema.
     *
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param SchemaManager $schemaManager
     * @param string $message
     */
    public function assertResponseAndRequestMatch(
        ResponseInterface $response,
        RequestInterface $request,
        SchemaManager $schemaManager,
        $message = ''
    ) {
        $this->assertRequestMatch($request, $schemaManager, $message);
        $this->assertResponseMatch($response, $schemaManager, $request->getPath(), $request->getMethod(), $message);
    }

    /**
     * @param string[] $headers
     *
     * @return string
     */
    protected function inlineHeaders(array $headers)
    {
        return array_map(
            function (array $headers) {
                return implode(', ', $headers);
            },
            $headers
        );
    }
}
