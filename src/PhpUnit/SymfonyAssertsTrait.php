<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Facade functions for interacting with Symfony/HttpFoundation constraints.
 */
trait SymfonyAssertsTrait
{
    use AssertsTrait;

    /**
     * Asserts response match with the response schema.
     *
     * @param Response $response
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertResponseMatch(
        Response $response,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
    ) {
        $this->assertResponseMediaTypeMatch(
            $response->headers->get('Content-Type'),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        $httpCode = $response->getStatusCode();
        $headers = $this->inlineHeaders($response->headers->all());

        $this->assertResponseHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );

        $this->assertResponseBodyMatch(
            json_decode($response->getContent()),
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
     * @param Request $request
     * @param SchemaManager $schemaManager
     * @param string $message
     */
    public function assertRequestMatch(
        Request $request,
        SchemaManager $schemaManager,
        $message = ''
    ) {
        $path = $request->getRequestUri();
        $httpMethod = $request->getMethod();

        $headers = $this->inlineHeaders($request->headers->all());

        $this->assertRequestHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        if (!empty((string) $request->getContent())) {
            $this->assertRequestMediaTypeMatch(
                $request->headers->get('Content-Type'),
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

        $this->assertRequestBodyMatch(
            json_decode($request->getContent()),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );
    }

    /**
     * Asserts response match with the response schema.
     *
     * @param Response $response
     * @param Request $request
     * @param SchemaManager $schemaManager
     * @param string $message
     */
    public function assertResponseAndRequestMatch(
        Response $response,
        Request $request,
        SchemaManager $schemaManager,
        $message = ''
    ) {
        try {
            $this->assertRequestMatch($request, $schemaManager, $message);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            // If response represent a Client error then ignore.
            $statusCode = $response->getStatusCode();
            if ($statusCode < 400 || $statusCode > 499) {
                throw $e;
            }
        }

        $this->assertResponseMatch($response, $schemaManager, $request->getRequestUri(), $request->getMethod(), $message);
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
