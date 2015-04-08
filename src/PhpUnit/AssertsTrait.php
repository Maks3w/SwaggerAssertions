<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_Assert as Assert;
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
     * @param SchemaManager $schemaManager
     * @param string $path
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

        $constraint = new ResponseBodyConstraint($schemaManager, $template, $httpMethod, $httpCode);

        Assert::assertThat($responseBody, $constraint, $message);
    }

    /**
     * Asserts response media type match with the media types defined.
     *
     * @param string $responseMediaType
     * @param SchemaManager $schemaManager
     * @param string $path
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
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        // Strip charset encoding
        $ctHeader = ContentType::fromString('Content-Type: ' . $responseMediaType);
        $responseMediaType = $ctHeader->getMediaType();

        $constraint = new ResponseMediaTypeConstraint($schemaManager, $template, $httpMethod);

        Assert::assertThat($responseMediaType, $constraint, $message);
    }

    /**
     * Asserts response headers match with the media types defined.
     *
     * @param string[] $headers
     * @param SchemaManager $schemaManager
     * @param string $path
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
            throw new \RuntimeException('Request URI does not match with any swagger path definition');
        }

        $constraint = new ResponseHeadersConstraint($schemaManager, $template, $httpMethod, $httpCode);

        Assert::assertThat($headers, $constraint, $message);
    }
}
