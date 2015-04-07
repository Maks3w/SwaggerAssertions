<?php

use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;

/**
 * PHPUnit-Guzzle integration example.
 */
class GuzzleTest extends \PHPUnit_Framework_TestCase
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

    public function testFetchPetMatchDefinition()
    {
        $client = new Client();
        $request = $client->createRequest('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request->addHeader('Accept', 'application/json');

        $response = $client->send($request);

        $this->assertResponseAndRequestMatch($response, $request, self::$schemaManager);
    }

    public function testOnlyResponse()
    {
        $client = new Client();
        $request = $client->createRequest('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request->addHeader('Accept', 'application/json');

        $response = $client->send($request);

        $this->assertResponseMatch($response, self::$schemaManager, '/pet/findByStatus', 'get');
    }
}
