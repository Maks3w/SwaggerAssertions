<?php

namespace FR3D\SwaggerAssertions\JsonSchema;

use FR3D\SwaggerAssertions\JsonSchema\Constraints\CollectionConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\EnumConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\FormatConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\NumberConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\ObjectConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\SchemaConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\StringConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\TypeConstraint;
use FR3D\SwaggerAssertions\JsonSchema\Constraints\UndefinedConstraint;
use JsonSchema\Validator as BaseValidator;

class Validator extends BaseValidator
{
    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org.
     *
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        $validator = new SchemaConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
    }

    /**
     * Validates an array.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkArray($value, $schema = null, $path = null, $i = null)
    {
        $validator = new CollectionConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Validates an object.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     * @param mixed $patternProperties
     */
    protected function checkObject($value, $schema = null, $path = null, $i = null, $patternProperties = null)
    {
        $validator = new ObjectConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i, $patternProperties);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Validates the type of a property.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkType($value, $schema = null, $path = null, $i = null)
    {
        $validator = new TypeConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a undefined element.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkUndefined($value, $schema = null, $path = null, $i = null)
    {
        $validator = new UndefinedConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a string element.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkString($value, $schema = null, $path = null, $i = null)
    {
        $validator = new StringConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a number element.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkNumber($value, $schema = null, $path = null, $i = null)
    {
        $validator = new NumberConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    /**
     * Checks a enum element.
     *
     * @param mixed $value
     * @param mixed $schema
     * @param mixed $path
     * @param mixed $i
     */
    protected function checkEnum($value, $schema = null, $path = null, $i = null)
    {
        $validator = new EnumConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }

    protected function checkFormat($value, $schema = null, $path = null, $i = null)
    {
        $validator = new FormatConstraint($this->checkMode, $this->uriRetriever);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }
}
