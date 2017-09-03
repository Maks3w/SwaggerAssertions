<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;

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

    public function __construct($expectedSchema, string $context, Validator $validator)
    {
        parent::__construct();

        $this->expectedSchema = $expectedSchema;
        $this->context = $context;
        $this->validator = $validator;
    }

    protected function matches($other)
    {
        if (isset($this->expectedSchema->type) && $this->expectedSchema->type === 'file') {
            return true;
        }

        $this->validator->reset();

        $this->validator->check($other, $this->expectedSchema);

        return $this->validator->isValid();
    }

    protected function failureDescription($other)
    {
        return json_encode($other) . ' ' . $this->toString();
    }

    protected function additionalFailureDescription($other)
    {
        $description = '';

        foreach ($this->validator->getErrors() as $error) {
            $description .= sprintf("[%s] %s\n", $error['property'], $error['message']);
        }

        return $description;
    }

    public function toString()
    {
        return 'is a valid ' . $this->context;
    }
}
