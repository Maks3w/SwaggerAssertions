<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\JsonSchemaConstraint
 */
class JsonSchemaConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $schema = <<<JSON
{
  "type":"array",
  "items":{
    "type":"object",
    "required":["id","name"],
    "externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},
    "properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}}
  }
}
JSON;
        $schema = json_decode($schema);

        $this->constraint = new JsonSchemaConstraint($schema, 'context', new Validator());
    }

    public function testConstraintDefinition()
    {
        $this->assertSame(1, count($this->constraint));
        $this->assertSame('is a valid context', $this->constraint->toString());
    }

    public function testValidSchema()
    {
        $response = <<<'JSON'
[
  {
    "id": 123456789,
    "name": "foo"
  }
]
JSON;
        $response = json_decode($response);

        $this->assertTrue($this->constraint->evaluate($response, '', true), $this->constraint->evaluate($response));
    }

    public function testInvalidSchema()
    {
        $response = <<<'JSON'
[
  {
    "id": 123456789
  }
]
JSON;
        $response = json_decode($response);

        $this->assertFalse($this->constraint->evaluate($response, '', true));

        try {
            $this->constraint->evaluate($response);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<'EOF'
Failed asserting that [{"id":123456789}] is a valid context.
[name] The property name is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
