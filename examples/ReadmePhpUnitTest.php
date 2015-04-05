<?php

use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;

/**
 * README.md PHPUnit example.
 */
class ReadmePhpUnitTest extends PHPUnit_Framework_TestCase
{
    use GuzzleAssertsTrait;

    /**
     * @var SchemaManager
     */
    protected static $schemaManager;

    public static function setUpBeforeClass()
    {
        self::$schemaManager = new SchemaManager('http://petstore.swagger.io/v2/swagger.json');

        // Use file:// for local files
        // self::$schemaManager = new SchemaManager('file:///MyAPI/swagger.json');
    }

    public function testFetchPetBodyMatchDefinition()
    {
        $client = new Client();
        $request = $client->createRequest('GET');
        $request->addHeader('Accept', 'application/json');
        $request->setPath('http://petstore.swagger.io/v2/pet/1');

        $response = $client->send($request);
        $responseBody = $response->json(['object' => true]);

        $this->assertResponseBodyMatch($responseBody, self::$schemaManager, '/pet/{petId}', 'get', 200);
    }

    public function testFetchPetMatchDefinition()
    {
        $client = new Client();
        $request = $client->createRequest('GET');
        $request->addHeader('Accept', 'application/json');
        $request->setPath('http://petstore.swagger.io/v2/pet/1');

        $response = $client->send($request);

        $this->assertResponseMatch($response, self::$schemaManager, '/pet/{petId}', 'get');
    }
}
