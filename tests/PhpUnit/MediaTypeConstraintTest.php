<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\MediaTypeConstraint;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestFailure as TestFailure;

/**
 * @covers FR3D\SwaggerAssertions\PhpUnit\MediaTypeConstraint
 */
class MediaTypeConstraintTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new MediaTypeConstraint(['application/json', 'text/xml']);
    }

    public function testConstraintDefinition()
    {
        self::assertEquals(1, count($this->constraint));
        self::assertEquals('is an allowed media type (application/json, text/xml)', $this->constraint->toString());
    }

    public function testValidMediaType()
    {
        self::assertTrue($this->constraint->evaluate('text/xml', '', true));
    }

    public function testInvalidMediaType()
    {
        $mediaType = 'application/pdf';
        self::assertFalse($this->constraint->evaluate($mediaType, '', true));

        try {
            $this->constraint->evaluate($mediaType);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that 'application/pdf' is an allowed media type (application/json, text/xml).

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
