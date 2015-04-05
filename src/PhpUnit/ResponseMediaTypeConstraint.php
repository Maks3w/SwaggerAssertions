<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_Constraint as Constraint;

/**
 * Validate response media type match against defined Swagger response media types.
 */
class ResponseMediaTypeConstraint extends Constraint
{
    /**
     * @var string[]
     */
    protected $mediaTypes;

    /**
     * @param SchemaManager $schemaManager
     * @param string $path
     * @param string $httpMethod
     */
    public function __construct(SchemaManager $schemaManager, $path, $httpMethod)
    {
        parent::__construct();

        $this->mediaTypes = $schemaManager->getResponseMediaTypes($path, $httpMethod);
    }

    protected function matches($other)
    {
        return in_array($other, $this->mediaTypes, true);
    }

    public function toString()
    {
        return 'is an allowed media type (' . implode(', ', $this->mediaTypes) . ')';
    }
}
