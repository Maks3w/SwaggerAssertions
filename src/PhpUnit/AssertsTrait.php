<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Validator;
use PHPUnit_Framework_Assert as Assert;
use stdClass;
use Zend\Http\Header\ContentType;

/**
 * Facade functions for interact with raw constraints.
 */
trait AssertsTrait
{
    /**
     * @var Factory
     */
    private $constraintFactory;

    /**
     * Asserts response body match with the response schema.
     *
     * @param stdClass|stdClass[] $responseBody
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param int $httpCode
     * @param string $message
     */
    public function assertResponseBodyMatch(
        $responseBody,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $httpCode,
        $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getResponseSchema($template, $httpMethod, $httpCode);
        $constraint = new JsonSchemaConstraint($bodySchema, 'response body', $this->getValidator());

        Assert::assertThat($responseBody, $constraint, $message);
    }

    /**
     * Asserts request body match with the request schema.
     *
     * @param stdClass|stdClass[] $requestBody
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertRequestBodyMatch(
        $requestBody,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $bodySchema = $schemaManager->getRequestSchema($template, $httpMethod);
        $constraint = new JsonSchemaConstraint($bodySchema, 'request body', $this->getValidator());

        Assert::assertThat($requestBody, $constraint, $message);
    }

    /**
     * Asserts response media type match with the media types defined.
     *
     * @param string $responseMediaType
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertResponseMediaTypeMatch(
        $responseMediaType,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        // Strip charset encoding
        $ctHeader = ContentType::fromString('Content-Type: ' . $responseMediaType);
        $responseMediaType = $ctHeader->getMediaType();

        $constraint = new MediaTypeConstraint($schemaManager->getResponseMediaTypes($template, $httpMethod));

        Assert::assertThat($responseMediaType, $constraint, $message);
    }

    /**
     * Asserts request media type match with the media types defined.
     *
     * @param string $requestMediaType
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertRequestMediaTypeMatch(
        $requestMediaType,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
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
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param int $httpCode
     * @param string $message
     */
    public function assertResponseHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $httpCode,
        $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new ResponseHeadersConstraint($schemaManager->getResponseHeaders($template, $httpMethod, $httpCode), $this->getValidator());

        Assert::assertThat($headers, $constraint, $message);
    }

    /**
     * Asserts request headers match with the media types defined.
     *
     * @param string[] $headers
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertRequestHeadersMatch(
        array $headers,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
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
     * Asserts request body match with the request schema.
     *
     * @param mixed[] $query
     * @param SchemaManager $schemaManager
     * @param string $path percent-encoded path used on the request.
     * @param string $httpMethod
     * @param string $message
     */
    public function assertRequestQueryMatch(
        $query,
        SchemaManager $schemaManager,
        $path,
        $httpMethod,
        $message = ''
    ) {
        if (!$schemaManager->findPathInTemplates($path, $template, $params)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
            // @codeCoverageIgnoreEnd
        }

        $constraint = new RequestQueryConstraint($schemaManager->getRequestQueryParameters($template, $httpMethod), $this->getValidator());

        Assert::assertThat($query, $constraint, $message);
    }

    /**
     * Returns a new Validator instance.
     *
     * @return Validator
     */
    protected function getValidator()
    {
        return new Validator(Constraint::CHECK_MODE_NORMAL, null, $this->constraintFactory);
    }
}
