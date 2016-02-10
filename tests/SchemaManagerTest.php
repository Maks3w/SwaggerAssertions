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
        $this->schemaManager = new SchemaManager('file://' . __DIR__ . '/fixture/petstore-with-external-docs.json');
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

        self::assertStringMatchesFormat($expectedSchema, json_encode($schema));
    }

    public function responseSchemaProvider()
    {
        $schema200 = '{"type":"array","items":{"required":["id","name"],"externalDocs":{"description":"find more info here","url":"https:\/\/swagger.io\/about"},"properties":{"id":{"type":"integer","format":"int64"},"name":{"type":"string"},"tag":{"type":"string"}},"id":"%s"}}';
        $schemaDefault = '{"required":["code","message"],"properties":{"code":{"type":"integer","format":"int32"},"message":{"type":"string"}},"id":"%s"}';

        $dataSet = [
            // Description => [path, method, httpCode, expectedSchema]
            'by http code' => ['/pets', 'get', 200, $schema200],
            'fallback to default' => ['/pets', 'get', 222, $schemaDefault],
        ];

        return $dataSet;
    }

    /**
     * @dataProvider responseHeadersProvider
     */
    public function testGetResponseHeaders($path, $method, $httpCode, $expectedHeaders)
    {
        $headers = $this->schemaManager->getResponseHeaders($path, $method, $httpCode);

        self::assertStringMatchesFormat($expectedHeaders, json_encode($headers));
    }

    public function responseHeadersProvider()
    {
        $dataSet = [
            // Description => [path, method, httpCode, expectedHeaders]
            'by http code' => ['/pets', 'get', 200, '{"ETag":{"minimum":1}}'],
            'fallback to default' => ['/pets', 'get', 222, '[]'],
        ];

        return $dataSet;
    }
}
