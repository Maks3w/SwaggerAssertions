<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\ResponseHeadersConstraint
 */
class ResponseHeadersConstraintTest extends TestCase
{
    /**
     * @var Constraint
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
        $this->assertSame(1, count($this->constraint));
        $this->assertSame('is a valid response header', $this->constraint->toString());
    }

    public function testValidHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'ETag' => '123',
        ];

        $this->assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testCaseInsensitiveValidHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'etag' => '123',
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
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
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
