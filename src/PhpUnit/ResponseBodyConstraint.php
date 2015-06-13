<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use JsonSchema\Validator;
use PHPUnit_Framework_Constraint as Constraint;

/**
 * Validate response body match against defined Swagger response schema.
 */
class ResponseBodyConstraint extends Constraint
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
     * @param string $path Swagger path template.
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
     * @param \stdClass $responseBody
     *
     * @return Validator
     */
    protected function getValidator($responseBody)
    {
        $responseSchema = $this->schemaManager->getResponseSchema($this->path, $this->httpMethod, $this->httpCode);

        $validator = new Validator();
        $validator->check($responseBody, $responseSchema);

        return $validator;
    }
}
