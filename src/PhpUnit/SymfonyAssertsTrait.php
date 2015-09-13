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

        $headers = $response->headers->all();
        foreach ($headers as &$value) {
            $value = implode(', ', $value);
        }

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
        $this->assertResponseMatch($response, $schemaManager, $request->getRequestUri(), $request->getMethod(), $message);
    }
}
