<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;

class GuzzleAssertsTraitTest extends TestCase
{
    use GuzzleAssertsTrait;

    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = new SchemaManager('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
    }

    public function testAssertResponseMatch()
    {
        $response = <<<JSON
[
  {
    "id": 123456789,
    "name": "foo"
  }
]
JSON;
        $response = new Response(200, [], Stream::factory($response));

        $this->assertResponseMatch($response, $this->schemaManager, '/pets', 'get');
    }

    public function testAssertResponseMatchFail()
    {
        $response = <<<JSON
[
  {
    "id": 123456789
  }
]
JSON;
        $response = new Response(200, [], Stream::factory($response));

        try {
            $this->assertResponseMatch($response, $this->schemaManager, '/pets', 'get');
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertTrue(true);
        }
    }
}
