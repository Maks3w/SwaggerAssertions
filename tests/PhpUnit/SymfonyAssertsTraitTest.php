<?php

namespace FR3D\SwaggerAssertionsTest\PhpUnit;

use FR3D\SwaggerAssertions\PhpUnit\SymfonyAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers FR3D\SwaggerAssertions\PhpUnit\SymfonyAssertsTrait
 */
class SymfonyAssertsTraitTest extends TestCase
{
    use SymfonyAssertsTrait;

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
            self::assertStringMatchesFormat(
                <<<EOF
Failed asserting that {"content-type":"application\/json"%s} is a valid response header.
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
Failed asserting that {"content-type":"application\/json","x-optional-header":"any"} is a valid request header.
[x-required-header] The property x-required-header is required

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
            'Content-Type' => 'application/json',
            'ETag' => '123',
        ];
    }

    /**
     * @param string $method
     * @param string $path
     * @param string[] $headers
     * @param string $body
     *
     * @return MockObject|Request
     */
    protected function createMockRequest($method, $path, array $headers, $body = '')
    {
        $request = Request::create($path, $method, [], [], [], [], $body);
        $request->headers = new HeaderBag($headers);

        return $request;
    }

    /**
     * @param int $statusCode
     * @param string[] $headers
     * @param string $body
     *
     * @return MockObject|Response
     */
    protected function createMockResponse($statusCode, array $headers, $body)
    {
        return new Response($body, $statusCode, $headers);
    }
}
