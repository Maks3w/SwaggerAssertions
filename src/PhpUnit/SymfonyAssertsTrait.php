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
    public static function assertResponseMatch(
        Response $response,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!empty((string) $response->getContent())) {
            self::assertResponseMediaTypeMatch(
                $response->headers->get('Content-Type'),
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

        $httpCode = $response->getStatusCode();
        $headers = self::inlineHeaders($response->headers->all());

        self::assertResponseHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $httpCode,
            $message
        );

        self::assertResponseBodyMatch(
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
     */
    public static function assertRequestMatch(
        Request $request,
        SchemaManager $schemaManager,
        string $message = ''
    ): void {
        $path = $request->getPathInfo();
        $httpMethod = $request->getMethod();
        $query = $request->query->all();

        $headers = self::inlineHeaders($request->headers->all());

        self::assertRequestHeadersMatch(
            $headers,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        if (!empty((string) $request->getContent())) {
            self::assertRequestMediaTypeMatch(
                $request->headers->get('Content-Type'),
                $schemaManager,
                $path,
                $httpMethod,
                $message
            );
        }

        self::assertRequestQueryMatch(
            $query,
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );

        self::assertRequestBodyMatch(
            json_decode($request->getContent()),
            $schemaManager,
            $path,
            $httpMethod,
            $message
        );
    }

    /**
     * Asserts response match with the response schema.
     */
    public static function assertResponseAndRequestMatch(
        Response $response,
        Request $request,
        SchemaManager $schemaManager,
        string $message = ''
    ): void {
        try {
            self::assertRequestMatch($request, $schemaManager, $message);
        } catch (ExpectationFailedException $e) {
            // If response represent a Client error then ignore.
            $statusCode = $response->getStatusCode();
            if ($statusCode < 400 || $statusCode > 499) {
                throw $e;
            }
        }

        self::assertResponseMatch($response, $schemaManager, $request->getRequestUri(), $request->getMethod(), $message);
    }

    /**
     * @param string[] $headers
     *
     * @return string[]
     */
    protected static function inlineHeaders(array $headers): array
    {
        return array_map(
            function (array $headers) {
                return implode(', ', $headers);
            },
            $headers
        );
    }
}
