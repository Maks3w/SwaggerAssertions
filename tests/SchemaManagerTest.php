<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions;

use PHPUnit\Framework\TestCase;

/**
 * @covers \FR3D\SwaggerAssertions\SchemaManager
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
            'integer' => ['/pets/1234', '/pets/{id}', ['id' => 1234]],
        ];

        $rfc3986AllowedPathCharacters = [
            '-', '.', '_', '~', '!', '$', '&', "'", '(', ')', '*', '+', ',', ';', '=', ':', '@',
        ];

        foreach ($rfc3986AllowedPathCharacters as $char) {
            $title = "RFC3986 path character ($char)";
            $title = str_replace("'", 'single quote', $title); // PhpStorm workaround

            $parameter = 'a' . $char . 'b';

            $data = ['/pets/' . $parameter, '/pets/{id}', ['id' => $parameter]];

            $dataCases[$title] = $data;
        }

        return $dataCases;
    }

    /**
     * @dataProvider responseMediaTypesProvider
     */
    public function testGetResponseMediaType($path, $method, int $httpStatusCode, array $expectedMediaTypes)
    {
        $mediaTypes = $this->schemaManager->getResponseMediaTypes($path, $method, $httpStatusCode);

        self::assertEquals($expectedMediaTypes, $mediaTypes);
    }

    public function responseMediaTypesProvider()
    {
        return [
            // Description => [path, method, status code, expectedMediaTypes]
            'in response object' => ['/pets/{id}', 'patch', '200', ['application/json']],
            'response without content' => ['/pets/{id}', 'patch', '204', []],
            'fallback to default' => ['/pets/{id}', 'patch', '400', ['application/json', 'text/html']],
        ];
    }

    /**
     * @dataProvider responseSchemaProvider
     */
    public function testGetResponseSchema($path, $method, $httpCode, $mediaType, $expectedSchema)
    {
        $schema = $this->schemaManager->getResponseSchema($path, $method, (string) $httpCode, $mediaType);

        self::assertEquals($expectedSchema, json_encode($schema));
    }

    public function responseSchemaProvider()
    {
        $schema200 = '{"type":"array","items":{"required":["id","name"],"externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},"properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}}}}';
        $schemaDefault = '{"required":["code","message"],"properties":{"code":{"type":"integer","format":"int32"},"message":{"type":"string"}}}';

        $dataSet = [
            // Description => [path, method, httpCode, media type, expectedSchema]
            'by http code' => ['/pets', 'get', 200, 'application/json', $schema200],
            'fallback to default' => ['/pets', 'get', 222, 'application/json', $schemaDefault],
            'schema not defined (empty)' => ['/pets/{id}', 'patch', 204, '', '{}'],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider responseHeadersProvider
     */
    public function testGetResponseHeaders($path, $method, $httpCode, $expectedHeaders)
    {
        $headers = $this->schemaManager->getResponseHeaders($path, $method, (string) $httpCode);

        self::assertEquals($expectedHeaders, json_encode($headers));
    }

    public function responseHeadersProvider()
    {
        $dataSet = [
            // Description => [path, method, httpCode, expectedHeaders]
            'by http code' => ['/pets', 'get', 200, '{"ETag":{"schema":{"type":"string","minimum":1}}}'],
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
            'in request method' => ['/pets/{id}', 'patch', ['application/json']],
        ];
    }

    /**
     * @dataProvider requestParameters
     */
    public function testGetRequestParameters($path, $method, $expectedParameters)
    {
        $parameters = $this->schemaManager->getRequestParameters($path, $method);

        self::assertEquals($expectedParameters, json_encode($parameters));
    }

    public function requestParameters()
    {
        $pets_id_shared_parameters = '[{"name":"id","in":"path","description":"ID of pet to fetch","required":true,"schema":{"type":"integer","format":"int64"}}';
        $pets_id_patch_parameters = $pets_id_shared_parameters . ',{"name":"X-Required-Header","in":"header","description":"Required header","required":true,"schema":{"type":"string"}},{"name":"X-Optional-Header","in":"header","description":"Optional header","schema":{"type":"string"}}]';
        $pets_id_delete_parameter = '[{"name":"id","in":"path","description":"Override the shared ID parameter","required":true,"schema":{"type":"integer","format":"int64"}}]';

        $dataSet = [
            // Description => [path, method, expectedParameters]
            'without parameters' => ['/food', 'get', '[]'],
            'with a shared parameter and operation parameters' => ['/pets/{id}', 'patch', $pets_id_patch_parameters],
            'with a operation parameter that overrides a shared parameter' => ['/pets/{id}', 'delete', $pets_id_delete_parameter],
            'with only a shared parameter' => ['/pets/{id}', 'get', $pets_id_shared_parameters . ']'],
        ];

        return $dataSet;
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
        $parameters = '[{"name":"X-Required-Header","in":"header","description":"Required header","required":true,"schema":{"type":"string"}},{"name":"X-Optional-Header","in":"header","description":"Optional header","schema":{"type":"string"}}]';

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
    public function testGetRequestBodyParameters($path, $method, $mediaType, $expectedParameters)
    {
        $parameters = $this->schemaManager->getRequestSchema($path, $method, $mediaType);

        self::assertEquals($expectedParameters, json_encode($parameters));
    }

    public function requestBodyParameters()
    {
        $parameters = '{"allOf":[{"required":["id","name"],"externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},"properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}}},{"required":["id"],"properties":{"id":{"type":"integer","format":"int64"}}}]}';

        $dataSet = [
            // Description => [path, method, media type, expectedBody]
            'in request method' => ['/pets/{id}', 'patch', 'application/json', $parameters],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider requestQueryParameters
     */
    public function testGetRequestQueryParameters($path, $method, $expectedParameters)
    {
        $parameters = $this->schemaManager->getRequestQueryParameters($path, $method);

        self::assertEquals($expectedParameters, json_encode($parameters));
    }

    public function requestQueryParameters()
    {
        $parameters = '[{"name":"tags","in":"query","description":"tags to filter by","required":false,"style":"simple","schema":{"type":"array","items":{"type":"string"}}},{"name":"limit","in":"query","description":"maximum number of results to return","required":true,"schema":{"type":"integer","format":"int32"}}]';

        $dataSet = [
            // Description => [path, method, expectedHeaders]
            'in request method' => ['/pets', 'get', $parameters],
            'without parameters' => ['/food', 'get', '[]'],
        ];

        return $dataSet;
    }
}
