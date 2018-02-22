<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use stdClass;
use Zend\Http\Header\ContentType;

/**
 * Facade functions for interact with raw constraints.
 */
trait AssertsTrait
{
    /**
     * Asserts response body match with the response schema.
     *
     * @param stdClass|stdClass[] $responseBody
     * @param string $path percent-encoded path used on the request.
     */
    public function assertResponseBodyMatch(
        $responseBody,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpCode,
        string $mediaType,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getResponseSchema($template, $httpMethod, (string) $httpCode, $mediaType);
        $constraint = new JsonSchemaConstraint($bodySchema, 'response body', $this->getValidator());

        Assert::assertThat($responseBody, $constraint, $message);
    }

    /**
     * Asserts request body match with the request schema.
     *
     * @param stdClass|stdClass[] $requestBody
     * @param string $path percent-encoded path used on the request.
     */
    public function assertRequestBodyMatch(
        $requestBody,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $mediaType,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getRequestSchema($template, $httpMethod, $mediaType);
        $constraint = new JsonSchemaConstraint($bodySchema, 'request body', $this->getValidator());

        Assert::assertThat($requestBody, $constraint, $message);
    }

    /**
     * Asserts response media type match with the media types defined.
     *
     * @param string $path percent-encoded path used on the request.
     */
    public function assertResponseMediaTypeMatch(
        string $responseMediaType,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpStatusCode,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        // Strip charset encoding
        $ctHeader = ContentType::fromString('Content-Type: ' . $responseMediaType);
        $responseMediaType = $ctHeader->getMediaType();

        $constraint = new MediaTypeConstraint($schemaManager->getResponseMediaTypes($template, $httpMethod, $httpStatusCode));

        Assert::assertThat($responseMediaType, $constraint, $message);
    }

    /**
     * Asserts request media type match with the media types defined.
     *
     * @param string $path percent-encoded path used on the request.
     */
    public function assertRequestMediaTypeMatch(
        string $requestMediaType,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        // Strip charset encoding
        $ctHeader = ContentType::fromString('Content-Type: ' . $requestMediaType);
        $requestMediaType = $ctHeader->getMediaType();

        $constraint = new MediaTypeConstraint($schemaManager->getRequestMediaTypes($template, $httpMethod));

        Assert::assertThat($requestMediaType, $constraint, $message);
    }

    /**
     * Asserts response headers match with the media types defined.
     *
     * @param string[] $headers
     * @param string $path percent-encoded path used on the request.
     */
    public function assertResponseHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        int $httpCode,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new ResponseHeadersConstraint(
            $schemaManager->getResponseHeaders($template, $httpMethod, (string) $httpCode),
            $this->getValidator()
        );

        Assert::assertThat($headers, $constraint, $message);
    }

    /**
     * Asserts request headers match with the media types defined.
     *
     * @param string[] $headers
     * @param string $path percent-encoded path used on the request.
     */
    public function assertRequestHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new RequestHeadersConstraint($schemaManager->getRequestHeadersParameters($template, $httpMethod), $this->getValidator());

        Assert::assertThat($headers, $constraint, $message);
    }

    /**
     * Asserts request query match with the request schema.
     *
     * @param mixed[] $query
     * @param string $path percent-encoded path used on the request.
     */
    public function assertRequestQueryMatch(
        $query,
        SchemaManager $schemaManager,
        string $path,
        string $httpMethod,
        string $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new RequestQueryConstraint($schemaManager->getRequestQueryParameters($template, $httpMethod), $this->getValidator());

        Assert::assertThat($query, $constraint, $message);
    }

    protected function getValidator(): Validator
    {
        return new Validator();
    }
}
