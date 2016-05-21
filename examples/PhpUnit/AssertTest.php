<?php

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * PHPUnit integration example.
 */
class AssertTest extends \PHPUnit_Framework_TestCase
{
    use AssertsTrait;

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

    public function testFetchPetBodyMatchDefinition()
    {
        $request = $this->guzzleHttpClient->createRequest('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request->addHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);
        $responseBody = $response->json(['object' => true]);

        $this->assertResponseBodyMatch($responseBody, self::$schemaManager, '/v2/pet/findByStatus', 'get', 200);
    }
}
