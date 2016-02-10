<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit_Framework_Constraint as Constraint;

/**
 * Validate given value match the expected JSON Schema.
 */
class JsonSchemaConstraint extends Constraint
{
    /**
     * @var object
     */
    protected $expectedSchema;

    /**
     * @var string
     */
    private $context;

    /**
     * @param object $expectedSchema
     * @param string $context
     */
    public function __construct($expectedSchema, $context)
    {
        parent::__construct();

        $this->expectedSchema = $expectedSchema;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other)
    {
        $validator = $this->getValidator($other);

        return $validator->isValid();
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other)
    {
        return json_encode($other) . ' ' . $this->toString();
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalFailureDescription($other)
    {
        $description = '';

        $validator = $this->getValidator($other);
        foreach ($validator->getErrors() as $error) {
            $description .= sprintf("[%s] %s\n", $error['property'], $error['message']);
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'is a valid ' . $this->context;
    }

    /**
     * @param object $schema
     *
     * @return Validator
     */
    protected function getValidator($schema)
    {
        $validator = new Validator();
        $validator->check($schema, $this->expectedSchema);

        return $validator;
    }
}
