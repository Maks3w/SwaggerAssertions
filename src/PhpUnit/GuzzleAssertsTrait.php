<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\ResponseInterface;
use PHPUnit_Framework_Assert as Assert;

/**
 * Facade functions for interact with Guzzle constraints.
 */
trait GuzzleAssertsTrait
{
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
        $constraint = new GuzzleResponseConstraint($schemaManager, $path, $httpMethod);

        Assert::assertThat($response, $constraint, $message);
    }
}
