<?php

declare(strict_types=1);

use FR3D\SwaggerAssertions\PhpUnit\Psr7AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit-Guzzle integration example.
 */
class Psr7WithGuzzleV6Test extends TestCase
{
    use Psr7AssertsTrait;

    /**
     * @var SchemaManager
     */
    protected static $schemaManager;

    /**
     * @var ClientInterface
     */
    protected $guzzleHttpClient;

    public static function setUpBeforeClass(): void
    {
        self::$schemaManager = SchemaManager::fromUri('http://petstore.swagger.io/v2/swagger.json');
    }

    protected function setUp(): void
    {
        $this->guzzleHttpClient = new Client(['headers' => ['User-Agent' => 'https://github.com/Maks3w/SwaggerAssertions']]);
    }

    public function testFetchPetMatchDefinition(): void
    {
        $request = new Request('GET', 'http://petstore.swagger.io/v2/store/inventory');
        $request = $request->withHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);

        self::assertResponseAndRequestMatch($response, $request, self::$schemaManager);
    }

    public function testOnlyResponse(): void
    {
        $request = new Request('GET', 'http://petstore.swagger.io/v2/pet/findByStatus');
        $request = $request->withHeader('Accept', 'application/json');

        $response = $this->guzzleHttpClient->send($request);

        self::assertResponseMatch($response, self::$schemaManager, '/v2/pet/findByStatus', 'get');
    }
}
