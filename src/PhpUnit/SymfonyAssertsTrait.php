<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit\Framework\ExpectationFailedException;
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
     * @param string $path percent-encoded path used on the request.
     */
    public function assertResponseMatch(
        Response $response,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpStatusCode,
        string $message = ''
    ) {
        if (!empty((string) $response->getContent())) {$responseMediaType =
            $response->headers->get('Content-Type');
$this->assertResponseMediaTypeMatch(
            $responseMediaType,            $schemaManager,
            $path,
            $httpMethod,$httpStatusCode,
            $message
        );}

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
            $responseMediaType,
            $message
        );
    }

    /**
     * Asserts request match with the request schema.
     */
    public function assertRequestMatch(
        Request $request,
        SchemaManager $schemaManager,
        string $message = ''
    ) {
        $path = $request->getPathInfo();
        $httpMethod = strtolower($request->getMethod());
        $query = $request->query->all();

        $headers = $this->inlineHeaders($request->headers->all());

        $this->assertRequestHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        $requestMediaType = $request->headers->get('Content-Type');

        if (!empty((string) $request->getContent())) {
            $this->assertRequestMediaTypeMatch(
                $requestMediaType,
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

        $this->assertRequestQueryMatch(
            $query,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        $this->assertRequestBodyMatch(
            json_decode($request->getContent()),
            $schemaManager,
            $path,
            $httpMethod,
            $requestMediaType,
            $message
        );
    }

    /**
     * Asserts response match with the response schema.
     */
    public function assertResponseAndRequestMatch(
        Response $response,
        Request $request,
        SchemaManager $schemaManager,
        string $message = ''
    ) {
        $statusCode = $response->getStatusCode();

        try {
            $this->assertRequestMatch($request, $schemaManager, $message);
        } catch (ExpectationFailedException $e) {
            // If response represent a Client error then ignore.
            if ($statusCode < 400 || $statusCode > 499) {
                throw $e;
            }
        }

        $this->assertResponseMatch($response, $schemaManager, $request->getRequestUri(), strtolower($request->getMethod()), $statusCode, $message);
    }

    /**
     * @param string[] $headers
     *
     * @return string[]
     */
    protected function inlineHeaders(array $headers): array
    {
        return array_map(
            function (array $headers) {
                return implode(', ', $headers);
            },
            $headers
        );
    }
}
