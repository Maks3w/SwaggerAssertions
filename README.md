# Swagger Assertions

[![Build Status](https://travis-ci.org/Maks3w/SwaggerAssertions.svg?branch=master)](https://travis-ci.org/Maks3w/SwaggerAssertions)
[![Coverage Status](https://coveralls.io/repos/Maks3w/SwaggerAssertions/badge.svg?branch=master)](https://coveralls.io/r/Maks3w/SwaggerAssertions?branch=master)

Test any API requests and responses match with the models described in the documentation.

This project is compatible with [Swagger 2](http://swagger.io/) spec definitions.

## Installing via Composer

You can use [Composer](https://getcomposer.org) .

```bash
composer require Maks3w/SwaggerAssertions
```

## Usage in PHPUnit

There are two traits for provide predefined helper functions for different assertions.

- [AssertsTrait](src/PhpUnit/AssertsTrait.php) For assert different parts of the response
- [GuzzleAssertsTrait](src/PhpUnit/GuzzleAssertsTrait.php) For assert [Guzzle](https://github.com/guzzle/guzzle) responses.

Example:

```php
<?php

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\PhpUnit\GuzzleAssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use GuzzleHttp\Client;

class ReadmePhpUnitTest extends PHPUnit_Framework_TestCase {
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
```

## FAQ

<dl>
  <dt>Q: Can this library validate my Swagger definition?</dt>
  <dd>A: No. This library validate your API responses against your Swagger definition.</dd>
</dl>

## License

  Code licensed under BSD 2 clauses terms & conditions.

  See [LICENSE.txt](LICENSE.txt) for more information.
