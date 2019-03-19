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

    protected function setUp(): void
    {
        $this->schemaManager = SchemaManager::fromUri('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
    }

    public function testAssertResponseBodyMatch(): void
    {
        $response = <<<'JSON'
{
  "id": 123456789,
  "name": "foo"
}
JSON;
        $response = json_decode($response);

        self::assertResponseBodyMatch($response, $this->schemaManager, '/api/pets/123456789', 'get', 200);
    }

    public function testAssertResponseBodyMatchWithFile(): void
    {
        $valid_gif_file = base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true);

        self::assertResponseBodyMatch($valid_gif_file, $this->schemaManager, '/api/pets/123456789/photo', 'get', 200);
    }

    public function testAssertResponseBodyMatchFail(): void
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
            self::assertResponseBodyMatch($response, $this->schemaManager, '/api/pets', 'get', 200);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidResponseMediaType(): void
    {
        self::assertResponseMediaTypeMatch('text/html', $this->schemaManager, '/api/pets', 'get');
    }

    public function testInvalidResponseMediaType(): void
    {
        try {
            self::assertResponseMediaTypeMatch('application/pdf', $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidResponseHeaders(): void
    {
        $headers = [
            'ETag' => '123',
        ];

        self::assertResponseHeadersMatch($headers, $this->schemaManager, '/api/pets', 'get', 200);
    }

    public function testInvalidResponseHeaders(): void
    {
        $headers = [];

        try {
            self::assertResponseHeadersMatch($headers, $this->schemaManager, '/api/pets', 'get', 200);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testAssertRequestBodyMatch(): void
    {
        $request = <<<'JSON'
{
  "id": 123456789,
  "name": "foo"
}
JSON;
        $request = json_decode($request);

        self::assertRequestBodyMatch($request, $this->schemaManager, '/api/pets', 'post');
    }

    public function testAssertRequestBodyMatchFail(): void
    {
        $request = <<<'JSON'
{
  "id": 123456789
}
JSON;
        $request = json_decode($request);

        try {
            self::assertRequestBodyMatch($request, $this->schemaManager, '/api/pets', 'post');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestMediaType(): void
    {
        self::assertRequestMediaTypeMatch('application/json', $this->schemaManager, '/api/pets', 'post');
    }

    public function testInvalidRequestMediaType(): void
    {
        try {
            self::assertRequestMediaTypeMatch('application/pdf', $this->schemaManager, '/api/pets', 'post');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestHeaders(): void
    {
        $headers = [
            'X-Required-Header' => 'any',
        ];

        self::assertRequestHeadersMatch($headers, $this->schemaManager, '/api/pets/1234', 'patch');
    }

    public function testInvalidRequestHeaders(): void
    {
        $headers = [];

        try {
            self::assertRequestHeadersMatch($headers, $this->schemaManager, '/api/pets/1234', 'patch');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }

    public function testValidRequestQuery(): void
    {
        $query = ['tags' => ['foo', 'bar'], 'limit' => 1];

        self::assertRequestQueryMatch($query, $this->schemaManager, '/api/pets', 'get');
    }

    public function testInvalidRequestQuery(): void
    {
        $query = ['tags' => ['foo', 'bar']];

        try {
            self::assertRequestQueryMatch($query, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertTrue(true);
        }
    }
}
