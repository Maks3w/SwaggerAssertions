<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\GuzzleResponseConstraint;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

class GuzzleResponseConstraintTest extends TestCase
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    /**
     * @var \PHPUnit_Framework_Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->schemaManager = new SchemaManager('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
        $this->constraint = new GuzzleResponseConstraint($this->schemaManager, '/pets', 'get');
    }

    public function testConstraintDefinition()
    {
        $this->assertEquals(1, count($this->constraint));
        $this->assertEquals('is valid', $this->constraint->toString());
    }

    public function testValidSchema()
    {
        $response = <<<JSON
[
  {
    "id": 123456789,
    "name": "foo"
  }
]
JSON;
        $response = new Response(200, [], Stream::factory($response));

        $this->assertTrue($this->constraint->evaluate($response, '', true), $this->constraint->evaluate($response));
    }

    public function testInvalidSchema()
    {
        $response = <<<JSON
[
  {
    "id": 123456789
  }
]
JSON;
        $response = new Response(200, [], Stream::factory($response));

        $this->assertFalse($this->constraint->evaluate($response, '', true));

        try {
            $this->constraint->evaluate($response);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that [{"id":123456789}] is valid.
[0] the property name is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testDefaultSchema()
    {
        $this->constraint = new GuzzleResponseConstraint($this->schemaManager, '/pets', 'get');

        $response = <<<JSON
{
  "code": 123456789,
  "message": "foo"
}
JSON;
        $response = new Response(222, [], Stream::factory($response));

        $this->assertTrue($this->constraint->evaluate($response, '', true), $this->constraint->evaluate($response));
    }
}
