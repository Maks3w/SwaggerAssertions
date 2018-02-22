<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FR3D\SwaggerAssertions\PhpUnit\AssertsTrait
 */
class AssertsTraitTest extends TestCase
{
    use AssertsTrait;

    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = SchemaManager::fromUri('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
    }

    public function testAssertResponseBodyMatch()
    {
        $response = <<<'JSON'
{
  "id": 123456789,
  "name": "foo"
}
JSON;
        $response = json_decode($response);

        self::assertResponseBodyMatch($response, $this->schemaManager, '/pets/123456789', 'get', 200, 'application/json');
    }

    public function testAssertResponseBodyMatchWithFile()
    {
        $valid_gif_file = base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true);

        self::assertResponseBodyMatch($valid_gif_file, $this->schemaManager, '/api/pets/123456789/photo', 'get', 200);
    }

    public function testAssertResponseBodyMatchFail()
    {
        $response = <<<'JSON'
[
  {
    "id": 123456789
  }
]
JSON;
        $response = json_decode($response);

        try {
            self::assertResponseBodyMatch($response, $this->schemaManager, '/pets', 'get', 200, 'application/json');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidResponseMediaType()
    {
        self::assertResponseMediaTypeMatch('application/json', $this->schemaManager, '/pets', 'get', 200);
    }

    public function testInvalidResponseMediaType()
    {
        try {
            self::assertResponseMediaTypeMatch('application/pdf', $this->schemaManager, '/pets', 'get', 200);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidResponseHeaders()
    {
        $headers = [
            'ETag' => '123',
        ];

        self::assertResponseHeadersMatch($headers, $this->schemaManager, '/pets', 'get', 200);
    }

    public function testInvalidResponseHeaders()
    {
        $headers = [];

        try {
            self::assertResponseHeadersMatch($headers, $this->schemaManager, '/pets', 'get', 200);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testAssertRequestBodyMatch()
    {
        $request = <<<'JSON'
{
  "id": 123456789,
  "name": "foo"
}
JSON;
        $request = json_decode($request);

        self::assertRequestBodyMatch($request, $this->schemaManager, '/pets', 'post', 'application/json');
    }

    public function testAssertRequestBodyMatchFail()
    {
        $request = <<<'JSON'
{
  "id": 123456789
}
JSON;
        $request = json_decode($request);

        try {
            self::assertRequestBodyMatch($request, $this->schemaManager, '/pets', 'post', 'application/json');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestMediaType()
    {
        self::assertRequestMediaTypeMatch('application/json', $this->schemaManager, '/pets', 'post');
    }

    public function testInvalidRequestMediaType()
    {
        try {
            self::assertRequestMediaTypeMatch('application/pdf', $this->schemaManager, '/pets', 'post');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestHeaders()
    {
        $headers = [
            'X-Required-Header' => 'any',
        ];

        self::assertRequestHeadersMatch($headers, $this->schemaManager, '/pets/1234', 'patch');
    }

    public function testInvalidRequestHeaders()
    {
        $headers = [];

        try {
            self::assertRequestHeadersMatch($headers, $this->schemaManager, '/pets/1234', 'patch');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestQuery()
    {
        $query = ['tags' => ['foo', 'bar'], 'limit' => 1];

        self::assertRequestQueryMatch($query, $this->schemaManager, '/pets', 'get');
    }

    public function testInvalidRequestQuery()
    {
        $query = ['tags' => ['foo', 'bar']];

        try {
            self::assertRequestQueryMatch($query, $this->schemaManager, '/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }
}
