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
        $response = $this->getValidResponseBody();
        $response = new Response(200, ['Content-Type' => 'application/json'], Stream::factory($response));

        $this->assertResponseMatch($response, $this->schemaManager, '/pets', 'get');
    }

    public function testAssertResponseBodyDoesNotMatch()
    {
        $response = <<<JSON
[
  {
    "id": 123456789
  }
]
JSON;
        $response = new Response(200, ['Content-Type' => 'application/json'], Stream::factory($response));

        try {
            $this->assertResponseMatch($response, $this->schemaManager, '/pets', 'get');
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that [{"id":123456789}] is valid.
[0] the property name is required

EOF
                ,
                $e->getMessage()
            );
        }
    }

    public function testAssertResponseMediaTypeDoesNotMatch()
    {
        $response = $this->getValidResponseBody();
        $response = new Response(200, ['Content-Type' => 'application/pdf'], Stream::factory($response));

        try {
            $this->assertResponseMatch($response, $this->schemaManager, '/pets', 'get');
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                "Failed asserting that 'application/pdf' is an allowed media type (application/json, application/xml, text/xml, text/html).",
                $e->getMessage()
            );
        }
    }

    /**
     * @return string
     */
    protected function getValidResponseBody()
    {
        return <<<JSON
[
  {
    "id": 123456789,
    "name": "foo"
  }
]
JSON;
    }
}
