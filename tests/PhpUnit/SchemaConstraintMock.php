<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use JsonSchema\Constraints\Constraint;

/**
 * JsonSchema constraint that always pass (it means that it does not create any error).
 */
class SchemaConstraintMock extends Constraint
{
    public function check($value, $schema = null, $path = null, $i = null)
    {
    }
}
