<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_Assert as Assert;
use stdClass;

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
        $constraint = new ResponseBodyConstraint($schemaManager, $path, $httpMethod, $httpCode);

        Assert::assertThat($responseBody, $constraint, $message);
    }
}
