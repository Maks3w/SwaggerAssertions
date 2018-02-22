<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\MediaTypeConstraint
 */
class MediaTypeConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new MediaTypeConstraint(['application/json', 'text/xml']);
    }

    public function testConstraintDefinition()
    {
        $this->assertSame(1, count($this->constraint));
        $this->assertSame('is an allowed media type (application/json, text/xml)', $this->constraint->toString());
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
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<'EOF'
Failed asserting that 'application/pdf' is an allowed media type (application/json, text/xml).

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
