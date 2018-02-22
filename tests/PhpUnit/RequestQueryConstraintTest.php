<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\RequestQueryConstraint
 */
class RequestQueryConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $constraint;

    protected function setUp()
    {
        $schema = '[{"name":"tags","in":"query","description":"tags to filter by","required":false,"type":"array","items":{"type":"string"},"collectionFormat":"csv"},{"name":"limit","in":"query","description":"maximum number of results to return","required":true,"type":"integer","format":"int32"}]';
        $schema = json_decode($schema);

        $this->constraint = new RequestQueryConstraint($schema, new Validator());
    }

    public function testConstraintDefinition()
    {
        $this->assertSame(1, count($this->constraint));
        $this->assertSame('is a valid request query', $this->constraint->toString());
    }

    public function testValidQuery()
    {
        $parameters = [
            'tags' => ['foo', 'bar'],
            'limit' => 1,
        ];

        $this->assertTrue($this->constraint->evaluate($parameters, '', true), $this->constraint->evaluate($parameters));
    }

    public function testInvalidParameterType()
    {
        $parameters = [
            'tags' => ['foo', 1],
            'limit' => 1,
        ];

        $this->assertFalse($this->constraint->evaluate($parameters, '', true));

        try {
            $this->constraint->evaluate($parameters);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<'EOF'
Failed asserting that {"tags":["foo",1],"limit":1} is a valid request query.
[tags[1]] Integer value found, but a string is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }

    public function testMissingParameter()
    {
        $parameters = [
            'tags' => ['foo', 'bar'],
        ];

        $this->assertFalse($this->constraint->evaluate($parameters, '', true));

        try {
            $this->constraint->evaluate($parameters);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<'EOF'
Failed asserting that {"tags":["foo","bar"]} is a valid request query.
[limit] The property limit is required

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }

    public function testConstructorDoesNotAlterParameters()
    {
        $source = '[{"name":"tags","in":"query","description":"tags to filter by","required":false,"type":"array","items":{"type":"string"},"collectionFormat":"csv"},{"name":"limit","in":"query","description":"maximum number of results to return","required":true,"type":"integer","format":"int32"}]';
        $schema = json_decode($source);
        $expected = json_decode($source);

        new RequestQueryConstraint($schema, new Validator());

        $this->assertEquals($expected, $schema);
    }
}
