<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\ResponseHeadersConstraint;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

class ResponseHeadersConstraintTest extends TestCase
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
        $this->constraint = new ResponseHeadersConstraint($this->schemaManager, '/pets', 'get', 200);
    }

    public function testConstraintDefinition()
    {
        $this->assertEquals(1, count($this->constraint));
        $this->assertEquals('is valid', $this->constraint->toString());
    }

    public function testValidHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'ETag' => '123',
        ];

        $this->assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testInvalidHeaderType()
    {
        $headers = [
            'Content-Type' => 'application/json',
            // 'ETag' => '123', // Removed intentional
        ];

        $this->assertFalse($this->constraint->evaluate($headers, '', true));

        try {
            $this->constraint->evaluate($headers);
            $this->fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that {"Content-Type":"application\/json"} is valid.
[] the property ETag is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
