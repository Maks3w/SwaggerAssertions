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
     * @var Validator
     */
    private $validator;

    /**
     * @param object $expectedSchema
     * @param string $context
     * @param Validator $validator
     */
    public function __construct($expectedSchema, $context, Validator $validator)
    {
        parent::__construct();

        $this->expectedSchema = $expectedSchema;
        $this->context = $context;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other)
    {
        $this->validator->reset();

        $this->check($other);

        return $this->validator->isValid();
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

        foreach ($this->validator->getErrors() as $error) {
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
     */
    protected function check($schema)
    {
        $this->validator->check($schema, $this->expectedSchema);
    }
}
