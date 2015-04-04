<?php

namespace FR3D\SwaggerAssertions\JsonSchema\Constraints;

use JsonSchema\Constraints\NumberConstraint as BaseNumberConstraint;

class NumberConstraint extends BaseNumberConstraint
{
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
