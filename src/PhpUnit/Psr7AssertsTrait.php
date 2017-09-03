<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Facade functions for interact with PSR7 Interfaces.
 */
trait Psr7AssertsTrait
{
    use AssertsTrait;

    /**
     * Asserts response match with the response schema.
     *
     * @param string $path percent-encoded path used on the request.
     */
    public function assertResponseMatch(
        ResponseInterface $response,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ) {
        if (!empty((string) $response->getBody())) {
            $this->assertResponseMediaTypeMatch(
                $response->getHeaderLine('Content-Type'),
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

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
            json_decode((string) $response->getBody()),
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );
    }

    /**
     * Asserts request match with the request schema.
     */
    public function assertRequestMatch(
        RequestInterface $request,
        SchemaManager $schemaManager,
        string $message = ''
    ) {
        $path = $request->getUri()->getPath();
        $httpMethod = $request->getMethod();

        $headers = $this->inlineHeaders($request->getHeaders());

        $queryString = $request->getUri()->getQuery();
        parse_str(html_entity_decode($queryString), $query);

        $this->assertRequestHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        if (!empty((string) $request->getBody())) {
            $this->assertRequestMediaTypeMatch(
                $request->getHeaderLine('Content-Type'),
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
            json_decode((string) $request->getBody()),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );
    }

    /**
     * Asserts response match with the response schema.
     */
    public function assertResponseAndRequestMatch(
        ResponseInterface $response,
        RequestInterface $request,
        SchemaManager $schemaManager,
        string $message = ''
    ) {
        try {
            $this->assertRequestMatch($request, $schemaManager, $message);
        } catch (ExpectationFailedException $e) {
            // If response represent a Client error then ignore.
            $statusCode = $response->getStatusCode();
            if ($statusCode < 400 || $statusCode > 499) {
                throw $e;
            }
        }

        $this->assertResponseMatch($response, $schemaManager, $request->getUri()->getPath(), $request->getMethod(), $message);
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
