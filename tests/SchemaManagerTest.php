<?php

namespace FR3D\SwaggerAssertionsTest;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers FR3D\SwaggerAssertions\SchemaManager
 */
class SchemaManagerTest extends TestCase
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = SchemaManager::fromUri('file://' . __DIR__ . '/fixture/petstore-with-external-docs.json');
    }

    /**
     * @dataProvider validPathsProvider
     */
    public function testFindPathInTemplatesValid($requestPath, $expectedTemplate, array $expectedParameters)
    {
        self::assertTrue($this->schemaManager->findPathInTemplates($requestPath, $path, $parameters));
        self::assertEquals($expectedTemplate, $path);
        self::assertEquals($expectedParameters, $parameters);
    }

    public function validPathsProvider()
    {
        $dataCases = [
            'integer' => ['/api/pets/1234', '/pets/{id}', ['id' => 1234]],
        ];

        $rfc3986AllowedPathCharacters = [
            '-', '.', '_', '~', '!', '$', '&', "'", '(', ')', '*', '+', ',', ';', '=', ':', '@',
        ];

        foreach ($rfc3986AllowedPathCharacters as $char) {
            $title = "RFC3986 path character ($char)";
            $title = str_replace("'", 'single quote', $title); // PhpStorm workaround

            $parameter = 'a' . $char . 'b';

            $data = ['/api/pets/' . $parameter, '/pets/{id}', ['id' => $parameter]];

            $dataCases[$title] = $data;
        }

        return $dataCases;
    }

    /**
     * @dataProvider responseMediaTypesProvider
     */
    public function testGetResponseMediaType($path, $method, array $expectedMediaTypes)
    {
        $mediaTypes = $this->schemaManager->getResponseMediaTypes($path, $method);

        self::assertEquals($expectedMediaTypes, $mediaTypes);
    }

    public function responseMediaTypesProvider()
    {
        return [
            // Description => [path, method, expectedMediaTypes]
            'in response object' => ['/pets', 'get', ['application/json', 'application/xml', 'text/xml', 'text/html']],
            'fallback to global' => ['/pets', 'delete', ['application/json']],
        ];
    }

    /**
     * @dataProvider responseSchemaProvider
     */
    public function testGetResponseSchema($path, $method, $httpCode, $expectedSchema)
    {
        $schema = $this->schemaManager->getResponseSchema($path, $method, $httpCode);

        self::assertEquals($expectedSchema, json_encode($schema));
    }

    public function responseSchemaProvider()
    {
        $schema200 = '{"type":"array","items":{"type":"object","required":["id","name"],"externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},"properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}}}}';
        $schemaDefault = '{"type":"object","required":["code","message"],"properties":{"code":{"type":"integer","format":"int32"},"message":{"type":"string"}}}';

        $dataSet = [
            // Description => [path, method, httpCode, expectedSchema]
            'by http code' => ['/pets', 'get', 200, $schema200],
            'fallback to default' => ['/pets', 'get', 222, $schemaDefault],
            'schema not defined (empty)' => ['/pets/{id}', 'patch', 204, '{}'],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider responseHeadersProvider
     */
    public function testGetResponseHeaders($path, $method, $httpCode, $expectedHeaders)
    {
        $headers = $this->schemaManager->getResponseHeaders($path, $method, $httpCode);

        self::assertEquals($expectedHeaders, json_encode($headers));
    }

    public function responseHeadersProvider()
    {
        $dataSet = [
            // Description => [path, method, httpCode, expectedHeaders]
            'by http code' => ['/pets', 'get', 200, '{"ETag":{"type":"string","minimum":1}}'],
            'fallback to default' => ['/pets', 'get', 222, '[]'],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider requestMediaTypesProvider
     */
    public function testGetRequestMediaType($path, $method, array $expectedMediaTypes)
    {
        $mediaTypes = $this->schemaManager->getRequestMediaTypes($path, $method);

        self::assertEquals($expectedMediaTypes, $mediaTypes);
    }

    public function requestMediaTypesProvider()
    {
        return [
            // Description => [path, method, expectedMediaTypes]
            'in request method' => ['/pets/{id}', 'patch', ['application/json', 'application/xml']],
            'fallback to global' => ['/pets', 'post', ['application/json']],
        ];
    }

    /**
     * @dataProvider requestHeadersParameters
     */
    public function testGetRequestHeadersParameters($path, $method, $expectedParameters)
    {
        $parameters = $this->schemaManager->getRequestHeadersParameters($path, $method);

        self::assertEquals($expectedParameters, json_encode($parameters));
    }

    public function requestHeadersParameters()
    {
        $parameters = '[{"name":"X-Required-Header","in":"header","description":"Required header","required":true,"type":"string"},{"name":"X-Optional-Header","in":"header","description":"Optional header","type":"string"}]';

        $dataSet = [
            // Description => [path, method, expectedHeaders]
            'in request method' => ['/pets/{id}', 'patch', $parameters],
            'without parameters' => ['/food', 'get', '[]'],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider requestBodyParameters
     */
    public function testGetRequestBodyParameters($path, $method, $expectedParameters)
    {
        $parameters = $this->schemaManager->getRequestSchema($path, $method);

        self::assertEquals($expectedParameters, json_encode($parameters));
    }

    public function requestBodyParameters()
    {
        $parameters = '{"type":"object","allOf":[{"type":"object","required":["id","name"],"externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},"properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}}},{"required":["id"],"properties":{"id":{"type":"integer","format":"int64"}}}]}';

        $dataSet = [
            // Description => [path, method, expectedBody]
            'in request method' => ['/pets/{id}', 'patch', $parameters],
            'without parameters' => ['/food', 'get', '{}'],
        ];

        return $dataSet;
    }
}
