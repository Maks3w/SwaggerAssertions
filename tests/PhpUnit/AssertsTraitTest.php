<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;

class AssertsTraitTest extends TestCase
{
    use AssertsTrait;

    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = new SchemaManager('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
    }

    public function testAssertResponseBodyMatch()
    {
        $response = <<<JSON
[
  {
    "id": 123456789,
    "name": "foo"
  }
]
JSON;
        $response = json_decode($response);

        $this->assertResponseBodyMatch($response, $this->schemaManager, '/pets', 'get', 200);
    }

    public function testAssertResponseBodyMatchFail()
    {
        $response = <<<JSON
[
  {
    "id": 123456789
  }
]
JSON;
        $response = json_decode($response);

        try {
            $this->assertResponseBodyMatch($response, $this->schemaManager, '/pets', 'get', 200);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertTrue(true);
        }
    }

    public function testValidMediaType()
    {
        $this->assertResponseMediaTypeMatch('text/html', $this->schemaManager, '/pets', 'get');
    }

    public function testInvalidMediaType()
    {
        try {
            $this->assertResponseMediaTypeMatch('application/pdf', $this->schemaManager, '/pets', 'get');
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertTrue(true);
        }
    }
}
