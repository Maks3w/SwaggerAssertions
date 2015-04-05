<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
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
     * @param string $path
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
        $headers = $response->getHeaders();
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
            $responseBody = $response->json(['object' => true]),
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );
    }
}
