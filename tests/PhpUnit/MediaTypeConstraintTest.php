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

    protected function setUp(): void
    {
        $this->constraint = new MediaTypeConstraint(['application/json', 'text/xml']);
    }

    public function testConstraintDefinition(): void
    {
        self::assertCount(1, $this->constraint);
        self::assertEquals('is an allowed media type (application/json, text/xml)', $this->constraint->toString());
    }

    public function testValidMediaType(): void
    {
        self::assertTrue($this->constraint->evaluate('text/xml', '', true));
    }

    public function testInvalidMediaType(): void
    {
        $mediaType = 'application/pdf';
        self::assertFalse($this->constraint->evaluate($mediaType, '', true));

        try {
            $this->constraint->evaluate($mediaType);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<'EOF'
Failed asserting that 'application/pdf' is an allowed media type (application/json, text/xml).

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
