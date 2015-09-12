<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\Psr7AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Psr7AssertsTraitTest extends TestCase
{
    use Psr7AssertsTrait;

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
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());

        self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
    }

    public function testAssertResponseAndRequestMatch()
    {
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());
        $request = $this->createMockRequest('GET', '/api/pets');

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
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $response);

        try {
            self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that [{"id":123456789}] is valid.
[name] The property name is required

EOF
                ,
                $e->getMessage()
            );
        }
    }

    public function testAssertResponseMediaTypeDoesNotMatch()
    {
        $response = $this->createMockResponse(
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

        $response = $this->createMockResponse(200, $headers, $this->getValidResponseBody());

        try {
            self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"Content-Type":"application\/json"} is valid.
[etag] The property etag is required

EOF
                ,
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
            'Content-Type' => [
                'application/json',
            ],
            'ETag' => [
                '123',
            ],
        ];
    }

    /**
     * @param string $method
     * @param string $path
     *
     * @return MockObject|RequestInterface
     */
    protected function createMockRequest($method, $path)
    {
        /** @var UriInterface|MockObject $request */
        $uri = $this->getMock('Psr\Http\Message\UriInterface');
        $uri->method('getPath')->willReturn($path);

        /** @var RequestInterface|MockObject $request */
        $request = $this->getMock('Psr\Http\Message\RequestInterface');
        $request->method('getMethod')->willReturn($method);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    /**
     * @param int $statusCode
     * @param array $headers
     * @param string $body
     *
     * @return MockObject|ResponseInterface
     */
    protected function createMockResponse($statusCode, array $headers, $body)
    {
        /** @var StreamInterface|MockObject $body */
        $stream = $this->getMock('Psr\Http\Message\StreamInterface');
        $stream->method('__toString')->willReturn($body);

        $headersMap = [];
        foreach ($headers as $headerName => $headerValues) {
            $headersMap[$headerName] = [$headerName, implode(', ', $headerValues)];
        }

        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeaderLine')->willReturnMap($headersMap);
        $response->method('getHeaders')->willReturn($headers);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }
}
