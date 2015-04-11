<?php

namespace FR3D\SwaggerAssertionsTest;

use FR3D\SwaggerAssertions\SchemaManager;
use PHPUnit_Framework_TestCase as TestCase;

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
     *
     * @param string $requestPath
     * @param string $expectedTemplate
     * @param array $expectedParameters
     */
    public function testFindPathInTemplatesValid($requestPath, $expectedTemplate, array $expectedParameters)
    {
        $this->assertTrue($this->schemaManager->findPathInTemplates($requestPath, $path, $parameters));
        $this->assertEquals($expectedTemplate, $path);
        $this->assertEquals($expectedParameters, $parameters);
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
}
