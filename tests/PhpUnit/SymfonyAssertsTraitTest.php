<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\SymfonyAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyAssertsTraitTest extends TestCase
{
    use SymfonyAssertsTrait;

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
        $response = $this->createResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());

        self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
    }

    public function testAssertResponseAndRequestMatch()
    {
        $response = $this->createResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());
        $request = $this->createReqyest('GET', '/api/pets');

        self::assertResponseAndRequestMatch($response, $request, $this->schemaManager);
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
        $response = $this->createResponse(200, $this->getValidHeaders(), $response);

        try {
            self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertRegExp(
                sprintf('/^Failed asserting that \[{.+}\] is valid.\\n\[%1$s\] The property %1$s is required\\n$/i', 'name'),
                $e->getMessage()
            );
        }
    }

    public function testAssertResponseMediaTypeDoesNotMatch()
    {
        $response = $this->createResponse(
            200,
            ['Content-Type' => ['application/pdf; charset=utf-8']],
            $this->getValidResponseBody()
        );

        try {
            self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                "Failed asserting that 'application/pdf' is an allowed media type (application/json, application/xml, text/xml, text/html).",
                $e->getMessage()
            );
        }
    }

    public function testAssertResponseHeaderDoesNotMatch()
    {
        $headers = [
            'Content-Type' => ['application/json'],
            // 'ETag' => ['123'], // Removed intentional
        ];

        $response = $this->createResponse(200, $headers, $this->getValidResponseBody());

        try {
            self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertRegExp(
                sprintf('/^Failed asserting that {.+} is valid.\\n\[%1$s\] The property %1$s is required\\n$/i', 'etag'),
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

    /**
     * @return string[]
     */
    protected function getValidHeaders()
    {
        return [
            'Content-Type' => 'application/json'
            ,
            'ETag' => '123'
            ,
        ];
    }

    /**
     * @param string $method
     * @param string $path
     *
     * @return Request
     */
    protected function createReqyest($method, $path)
    {
        return Request::create($path, $method);
    }

    /**
     * @param int $statusCode
     * @param array $headers
     * @param string $body
     *
     * @return Response
     */
    protected function createResponse($statusCode, array $headers, $body)
    {
        return new Response($body, $statusCode, $headers);
    }
}
