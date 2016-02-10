<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\RequestHeadersConstraint;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

/**
 * @covers FR3D\SwaggerAssertions\PhpUnit\RequestHeadersConstraint
 */
class RequestHeadersConstraintTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $schema = '[{"name":"X-Required-Header","in":"header","description":"Required header","required":true,"type":"string"},{"name":"X-Optional-Header","in":"header","description":"Optional header","type":"string"}]';
        $schema = json_decode($schema);

        $this->constraint = new RequestHeadersConstraint($schema);
    }

    public function testConstraintDefinition()
    {
        self::assertEquals(1, count($this->constraint));
        self::assertEquals('is a valid request header', $this->constraint->toString());
    }

    public function testValidHeaders()
    {
        $headers = [
            'X-Required-Header' => 'any',
        ];

        self::assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testCaseInsensitiveValidHeaders()
    {
        $headers = [
            'X-required-HEADER' => 'application/json',
        ];

        self::assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testInvalidHeaderType()
    {
        $headers = [
            'X-Optional-Header' => 'any',
        ];

        self::assertFalse($this->constraint->evaluate($headers, '', true));

        try {
            $this->constraint->evaluate($headers);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"X-Optional-Header":"any"} is a valid request header.
[x-required-header] The property x-required-header is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
