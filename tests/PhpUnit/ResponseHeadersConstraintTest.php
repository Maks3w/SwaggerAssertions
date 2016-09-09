<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\ResponseHeadersConstraint;
use JsonSchema\Validator;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

/**
 * @covers FR3D\SwaggerAssertions\PhpUnit\ResponseHeadersConstraint
 */
class ResponseHeadersConstraintTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $schema = '{"ETag":{"minimum":1}}';
        $schema = json_decode($schema);

        $this->constraint = new ResponseHeadersConstraint($schema, new Validator());
    }

    public function testConstraintDefinition()
    {
        self::assertEquals(1, count($this->constraint));
        self::assertEquals('is a valid response header', $this->constraint->toString());
    }

    public function testValidHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'ETag' => '123',
        ];

        $this->constraint->evaluate($headers);
        self::assertTrue(true);
    }

    public function testCaseInsensitiveValidHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'etag' => '123',
        ];

        $this->constraint->evaluate($headers);
        self::assertTrue(true);
    }

    public function testInvalidHeaderType()
    {
        $headers = [
            'Content-Type' => 'application/json',
            // 'ETag' => '123', // Removed intentional
        ];

        try {
            $this->constraint->evaluate($headers);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"Content-Type":"application\/json"} is a valid response header.
[etag] The property etag is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
