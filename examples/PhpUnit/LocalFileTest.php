<?php

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * PHPUnit integration example.
 */
class LocalFileTest extends \PHPUnit_Framework_TestCase
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
        $filePath = __DIR__ . '/../fixtures/pet_store.json';

        // Use file:// for local files
        self::$schemaManager = new SchemaManager(json_decode(file_get_contents($filePath)));
    }

    protected function setUp()
    {
        if (version_compare(ClientInterface::VERSION, '6.0', '>=')) {
            self::markTestSkipped('This example requires Guzzle V5 installed');
        }
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
