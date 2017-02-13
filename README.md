# Swagger Assertions

Test any API requests and responses match with the models described in the documentation.

This project is compatible with [Swagger 2](http://swagger.io/) spec definitions.

## Installing via Composer

You can use [Composer](https://getcomposer.org) .

```bash
composer require fr3d/swagger-assertions
```

## Usage in PHPUnit

There are two traits for provide predefined helper functions for different assertions.

- [AssertsTrait](src/PhpUnit/AssertsTrait.php) For assert different parts of the response
- [Psr7AssertsTrait](src/PhpUnit/Psr7AssertsTrait.php) For assert [PSR-7 compatible](http://www.php-fig.org/psr/psr-7/) responses.
- [SymfonyAssertsTrait](src/PhpUnit/SymfonyAssertsTrait.php) For assert [Symfony HTTP Foundation](http://symfony.com/doc/current/components/http_foundation/index.html) responses.

See examples at [examples/PhpUnit](examples/PhpUnit)

## FAQ

<dl>
  <dt>Q: Can this library validate my Swagger definition?</dt>
  <dd>A: No. This library validate your API requests and responses match your Swagger definition.</dd>
</dl>

## License

  Code licensed under BSD 2 clauses terms & conditions.

  See [LICENSE.txt](LICENSE.txt) for more information.
