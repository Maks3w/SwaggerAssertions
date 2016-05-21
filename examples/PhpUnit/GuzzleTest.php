<?php

use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

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

    /**
     * @var ClientInterface
     */
    protected $guzzleHttpClient;

    public static function setUpBeforeClass()
    {
        if (version_compare(ClientInterface::VERSION, '6.0', '>=')) {
            self::markTestSkipped('This example requires Guzzle V5 installed');
        }
        self::$schemaManager = SchemaManager::fromUri('http://petstore.swagger.io/v2/swagger.json');
    }

    protected function setUp()
    {
        $this->guzzleHttpClient = new Client(['headers' => ['User-Agent' => 'https://github.com/Maks3w/SwaggerAssertions']]);
    }

    public function testFetchPetMatchDefinition()
    {
        $request = $this->guzzleHttpClient->createRequest('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request->addHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);

        $this->assertResponseAndRequestMatch($response, $request, self::$schemaManager);
    }

    public function testOnlyResponse()
    {
        $request = $this->guzzleHttpClient->createRequest('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request->addHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);

        $this->assertResponseMatch($response, self::$schemaManager, '/v2/pet/findByStatus', 'get');
    }
}
