<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Query;
use GuzzleHttp\Stream\StreamInterface;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait
 */
class GuzzleAssertsTraitTest extends TestCase
{
    use GuzzleAssertsTrait;

    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = SchemaManager::fromUri('file://' . __DIR__ . '/../fixture/petstore-with-external-docs.json');
    }

    public function testAssertResponseMatch()
    {
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());

        self::assertResponseMatch($response, $this->schemaManager, '/api/pets', 'get');
    }

    public function testAssertResponseAndRequestMatch()
    {
        $body = $this->getValidRequestBody();
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $body);
        $request = $this->createMockRequest('POST', '/api/pets', ['Content-Type' => ['application/json']], $body);

        self::assertResponseAndRequestMatch($response, $request, $this->schemaManager);
    }

    public function testAssertResponseIsValidIfClientErrorAndRequestIsInvalid()
    {
        $response = $this->createMockResponse(404, $this->getValidHeaders(), '{"code":400,"message":"Invalid"}');
        $request = $this->createMockRequest('POST', '/api/pets', ['Content-Type' => ['application/pdf']]);

        self::assertResponseAndRequestMatch($response, $request, $this->schemaManager);
    }

    public function testAssertRerquestIsInvalidIfResponseIsNotAClientError()
    {
        $response = $this->createMockResponse(200, $this->getValidHeaders(), $this->getValidResponseBody());
        $request = $this->createMockRequest('POST', '/api/pets', ['Content-Type' => ['application/pdf']]);

        try {
            self::assertResponseAndRequestMatch($response, $request, $this->schemaManager);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            self::assertContains('request', $e->getMessage());
        }
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
Failed asserting that [{"id":123456789}] is a valid response body.
[[0].name] The property name is required

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
Failed asserting that {"Content-Type":"application\/json"} is a valid response header.
[etag] The property etag is required

EOF
                ,
                $e->getMessage()
            );
        }
    }

    public function testAssertRequestBodyDoesNotMatch()
    {
        $request = <<<JSON
{
  "id": 123456789
}
JSON;
        $request = $this->createMockRequest('POST', '/api/pets', $this->getValidHeaders(), $request);

        try {
            self::assertRequestMatch($request, $this->schemaManager);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"id":123456789} is a valid request body.
[name] The property name is required
[] Failed to match all schemas

EOF
                ,
                $e->getMessage()
            );
        }
    }

    public function testAssertRequestMediaTypeDoesNotMatch()
    {
        $request = $this->createMockRequest(
            'POST',
            '/api/pets',
            ['Content-Type' => ['application/pdf; charset=utf-8']],
            $this->getValidRequestBody()
        );

        try {
            self::assertRequestMatch($request, $this->schemaManager);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                "Failed asserting that 'application/pdf' is an allowed media type (application/json).",
                $e->getMessage()
            );
        }
    }

    public function testAssertRequestHeaderDoesNotMatch()
    {
        $headers = [
            'Content-Type' => ['application/json'],
            'X-Optional-Header' => ['any'],
        ];

        $request = $this->createMockRequest('PATCH', '/api/pets/123', $headers, $this->getValidRequestBody());

        try {
            self::assertRequestMatch($request, $this->schemaManager);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"Content-Type":"application\/json","X-Optional-Header":"any"} is a valid request header.
[x-required-header] The property x-required-header is required

EOF
                ,
                $e->getMessage()
            );
        }
    }

    public function testAssertRequestQueryDoesNotMatch()
    {
        $query = [
            'tags' => ['foo', '1'],
        ];

        $request = $this->createMockRequest('GET', '/api/pets', $this->getValidHeaders(), $this->getValidRequestBody(), $query);

        try {
            self::assertRequestMatch($request, $this->schemaManager);
            self::fail('Expected ExpectationFailedException to be thrown');
        } catch (ExpectationFailedException $e) {
            self::assertEquals(
                <<<EOF
Failed asserting that {"tags":["foo","1"]} is a valid request query.
[limit] The property limit is required

EOF
                ,
                $e->getMessage()
            );
        }
    }

    /**
     * @return string
     */
    protected function getValidRequestBody()
    {
        return <<<JSON
{
"id": 123456789,
"name": "foo"
}
JSON;
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
     * @param string[] $headers
     * @param string $body
     * @param mixed[] $query
     *
     * @return MockObject|RequestInterface
     */
    protected function createMockRequest($method, $path, array $headers, $body = '', $query = [])
    {
        $headersMap = $this->transformHeadersToMap($headers);

        /** @var RequestInterface|MockObject $request */
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $request->method('getHeader')->willReturnMap($headersMap);
        $request->method('getHeaders')->willReturn($headers);
        $request->method('getMethod')->willReturn($method);
        $request->method('getPath')->willReturn($path);
        $request->method('getBody')->willReturn($this->createMockStream($body));
        $request->method('getQuery')->willReturn(new Query($query));

        return $request;
    }

    /**
     * @param int $statusCode
     * @param string[] $headers
     * @param string $body
     *
     * @return MockObject|ResponseInterface
     */
    protected function createMockResponse($statusCode, array $headers, $body)
    {
        $headersMap = $this->transformHeadersToMap($headers);

        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeader')->willReturnMap($headersMap);
        $response->method('getHeaders')->willReturn($headers);
        $response->method('getBody')->willReturn($this->createMockStream($body));

        return $response;
    }

    /**
     * @param string $body
     *
     * @return StreamInterface|MockObject
     */
    protected function createMockStream($body)
    {
        /** @var StreamInterface|MockObject $stream */
        $stream = $this->getMockBuilder(StreamInterface::class)->getMock();
        $stream->method('__toString')->willReturn($body);

        return $stream;
    }

    /**
     * @param string[] $headers
     *
     * @return array
     */
    private function transformHeadersToMap(array $headers)
    {
        $headersMap = [];
        foreach ($headers as $headerName => $headerValues) {
            $headersMap[$headerName] = [$headerName, implode(', ', $headerValues)];
        }

        return $headersMap;
    }
}
