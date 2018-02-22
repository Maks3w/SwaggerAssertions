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
class AssertTest extends TestCase
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
        self::$schemaManager = SchemaManager::fromUri('http://petstore.swagger.io/v2/swagger.json');
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

        $this->assertResponseBodyMatch($responseBody, self::$schemaManager, '/v2/pet/findByStatus', 'get', 200, 'application/json');
    }
}
