<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\ResponseMediaTypeConstraint;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

class ResponseMediaTypeConstraintTest extends TestCase
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
        $this->constraint = new ResponseMediaTypeConstraint($this->schemaManager, '/pets', 'get');
    }

    public function testConstraintDefinition()
    {
        $this->assertEquals(1, count($this->constraint));
        $this->assertEquals('is an allowed media type (application/json, application/xml, text/xml, text/html)', $this->constraint->toString());
    }

    public function testValidMediaType()
    {
        $this->assertTrue($this->constraint->evaluate('text/xml', '', true));
    }

    public function testInvalidMediaType()
    {
        $mediaType = 'application/pdf';
        $this->assertFalse($this->constraint->evaluate($mediaType, '', true));

        try {
            $this->constraint->evaluate($mediaType);
            $this->fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'application/pdf' is an allowed media type (application/json, application/xml, text/xml, text/html).

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }

    public function testDefaultMediaType()
    {
        $this->constraint = new ResponseMediaTypeConstraint($this->schemaManager, '/pets', 'delete');

        $this->assertTrue($this->constraint->evaluate('application/json', '', true));
    }
}
