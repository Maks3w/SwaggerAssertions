<?php

declare(strict_types=1);

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit integration example.
 */
class LocalFileTest extends TestCase
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
        $this->guzzleHttpClient = new Client(['headers' => ['User-Agent' => 'https://github.com/Maks3w/SwaggerAssertions']]);
    }

    public function testFetchPetBodyMatchDefinition()
    {
        $request = new Request('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request = $request->withHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);

        $responseBody = json_decode((string) $response->getBody());

        $this->assertResponseBodyMatch($responseBody, self::$schemaManager, '/v2/pet/findByStatus', 'get', 200);
    }
}
