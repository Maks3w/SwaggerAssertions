<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\JsonSchema\Validator;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_Constraint as Constraint;

/**
 * Validate response headers match against defined Swagger response schema.
 */
class ResponseHeadersConstraint extends Constraint
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var int
     */
    protected $httpCode;

    /**
     * @param SchemaManager $schemaManager
     * @param string $path
     * @param string $httpMethod
     * @param int $httpCode
     */
    public function __construct(SchemaManager $schemaManager, $path, $httpMethod, $httpCode)
    {
        parent::__construct();

        $this->schemaManager = $schemaManager;
        $this->path = $path;
        $this->httpMethod = $httpMethod;
        $this->httpCode = $httpCode;
    }

    protected function matches($other)
    {
        $validator = $this->getValidator($other);

        return $validator->isValid();
    }

    protected function failureDescription($other)
    {
        return json_encode($other) . ' ' . $this->toString();
    }

    protected function additionalFailureDescription($other)
    {
        $description = '';

        $validator = $this->getValidator($other);
        foreach ($validator->getErrors() as $error) {
            $description .= sprintf("[%s] %s\n", $error['property'], $error['message']);
        }

        return $description;
    }

    public function toString()
    {
        return 'is valid';
    }

    /**
     * @param \stdClass $headers
     *
     * @return Validator
     */
    protected function getValidator($headers)
    {
        $schema = new \stdClass();
        $schema->properties = $this->schemaManager->getResponseHeaders($this->path, $this->httpMethod, $this->httpCode);
        $schema->required = array_keys((array) $schema->properties);

        $validator = new Validator();
        $validator->check((object) $headers, $schema);

        return $validator;
    }
}
