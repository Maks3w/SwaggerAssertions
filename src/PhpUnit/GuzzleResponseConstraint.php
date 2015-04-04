<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\ResponseInterface;
use PHPUnit_Framework_Constraint as Constraint;
use PHPUnit_Util_InvalidArgumentHelper as InvalidArgumentHelper;

/**
 * Validate response match against defined Swagger response schema.
 */
class GuzzleResponseConstraint extends Constraint
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    /**
     * @param SchemaManager $schemaManager
     * @param string $path
     * @param string $httpMethod
     */
    public function __construct(SchemaManager $schemaManager, $path, $httpMethod)
    {
        parent::__construct();

        $this->schemaManager = $schemaManager;
        $this->path = $path;
        $this->httpMethod = $httpMethod;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof ResponseInterface)) {
            throw InvalidArgumentHelper::factory(1, 'GuzzleHttp\Message\ResponseInterface');
        }

        $response = $other;

        $responseBody = $response->json(['object' => true]);
        $httpCode = $response->getStatusCode();
        $constraint = new ResponseBodyConstraint($this->schemaManager, $this->path, $this->httpMethod, $httpCode);

        return $constraint->evaluate($responseBody, $description, $returnResult);
    }

    public function toString()
    {
        return 'is valid';
    }
}
