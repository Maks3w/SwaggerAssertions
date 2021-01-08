<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;

/**
 * Facade functions for interact with raw constraints.
 */
trait AssertsTrait
{
    /**
     * Asserts response body match with the response schema.
     *
     * @param mixed $responseBody
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertResponseBodyMatch(
        $responseBody,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpCode,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getResponseSchema($template, $httpMethod, (string) $httpCode);
        $constraint = new JsonSchemaConstraint($bodySchema, 'response body', self::getValidator());

        Assert::assertThat($responseBody, $constraint, $message);
    }

    /**
     * Asserts request body match with the request schema.
     *
     * @param mixed $requestBody
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertRequestBodyMatch(
        $requestBody,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getRequestSchema($template, $httpMethod);
        $constraint = new JsonSchemaConstraint($bodySchema, 'request body', self::getValidator());

        Assert::assertThat($requestBody, $constraint, $message);
    }

    /**
     * Asserts response media type match with the media types defined.
     *
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertResponseMediaTypeMatch(
        string $responseMediaType,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        // Strip charset encoding
        $responseMediaType = self::getMediaType($responseMediaType);

        $constraint = new MediaTypeConstraint($schemaManager->getResponseMediaTypes($template, $httpMethod));

        Assert::assertThat($responseMediaType, $constraint, $message);
    }

    /**
     * Asserts request media type match with the media types defined.
     *
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertRequestMediaTypeMatch(
        string $requestMediaType,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        // Strip charset encoding
        $requestMediaType = self::getMediaType($requestMediaType);

        $constraint = new MediaTypeConstraint($schemaManager->getRequestMediaTypes($template, $httpMethod));

        Assert::assertThat($requestMediaType, $constraint, $message);
    }

    /**
     * Asserts response headers match with the media types defined.
     *
     * @param string[] $headers
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertResponseHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpCode,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new ResponseHeadersConstraint(
            $schemaManager->getResponseHeaders($template, $httpMethod, (string) $httpCode),
            self::getValidator()
        );

        Assert::assertThat($headers, $constraint, $message);
    }

    /**
     * Asserts request headers match with the media types defined.
     *
     * @param string[] $headers
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertRequestHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new RequestHeadersConstraint($schemaManager->getRequestHeadersParameters($template, $httpMethod), self::getValidator());

        Assert::assertThat($headers, $constraint, $message);
    }

    /**
     * Asserts request query match with the request schema.
     *
     * @param mixed[] $query
     * @param string $path percent-encoded path used on the request.
     */
    public static function assertRequestQueryMatch(
        $query,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ): void {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new RequestQueryConstraint($schemaManager->getRequestQueryParameters($template, $httpMethod), self::getValidator());

        Assert::assertThat($query, $constraint, $message);
    }

    protected static function getValidator(): Validator
    {
        return new Validator();
    }

    protected static function getMediaType(string $contentTypeHeader): string
    {
        if (trim($contentTypeHeader) === '') {
            return '';
        }

        $contentTypeParts = explode(';', $contentTypeHeader);
        return strtolower(trim($contentTypeParts[0]));
    }
}
