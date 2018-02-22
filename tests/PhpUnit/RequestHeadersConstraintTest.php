<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\RequestHeadersConstraint
 */
class RequestHeadersConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $constraint;
    const TEST_SCHEMA = '[{"name":"X-Required-Header","in":"header","description":"Required header","required":true,"type":"string"},{"name":"X-Optional-Header","in":"header","description":"Optional header","type":"string"}]';

    protected function setUp()
    {
        $schema = json_decode(self::TEST_SCHEMA);

        $this->constraint = new RequestHeadersConstraint($schema, new Validator());
    }

    public function testConstraintDefinition()
    {
        $this->assertSame(1, count($this->constraint));
        $this->assertSame('is a valid request header', $this->constraint->toString());
    }

    public function testValidHeaders()
    {
        $headers = [
            'X-Required-Header' => 'any',
        ];

        $this->assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testCaseInsensitiveValidHeaders()
    {
        $headers = [
            'X-required-HEADER' => 'application/json',
        ];

        $this->assertTrue($this->constraint->evaluate($headers, '', true), $this->constraint->evaluate($headers));
    }

    public function testInvalidHeaderType()
    {
        $headers = [
            'X-Optional-Header' => 'any',
        ];

        $this->assertFalse($this->constraint->evaluate($headers, '', true));

        try {
            $this->constraint->evaluate($headers);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<'EOF'
Failed asserting that {"X-Optional-Header":"any"} is a valid request header.
[x-required-header] The property x-required-header is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }

    public function testSchemaUnchanged()
    {
        $schema = json_decode(self::TEST_SCHEMA);
        new RequestHeadersConstraint($schema, new Validator());

        // Make sure there were no side effects ($schema should be unchanged)
        $this->assertEquals($schema, json_decode(self::TEST_SCHEMA));
    }
}
