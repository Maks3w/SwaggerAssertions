<?php

namespace FR3D\SwaggerAssertions;

use FR3D\SwaggerAssertions\JsonSchema\Uri\UriRetriever;
use InvalidArgumentException;
use JsonSchema\RefResolver;
use stdClass;

/**
 * Expose methods for navigate across the Swagger definition schema.
 */
class SchemaManager
{
    /**
     * Swagger definition.
     *
     * @var stdClass
     */
    protected $definition;

    /**
     * Swagger definition URI.
     *
     * @var string
     */
    protected $definitionUri;

    /**
     * @param string $definitionUri
     */
    public function __construct($definitionUri)
    {
        $this->definition = json_decode(file_get_contents($definitionUri));
        $this->definitionUri = $definitionUri;
    }

    /**
     * @param string $path
     * @param string $method
     * @param string $httpCode
     *
     * @return stdClass
     */
    public function getResponseSchema($path, $method, $httpCode)
    {
        $pathSegments = function ($path, $method, $httpCode) {
            return [
                'paths',
                $path,
                $method,
                'responses',
                $httpCode,
                'schema'
            ];
        };

        if ($this->hasPath($pathSegments($path, $method, $httpCode))) {
            $schema = $this->getPath($pathSegments($path, $method, $httpCode));
        } else {
            $schema = $this->getPath($pathSegments($path, $method, 'default'));
        }

        return $this->resolveSchemaReferences($schema);
    }

    /**
     * @param string[] $segments
     *
     * @return bool If path exists.
     */
    public function hasPath(array $segments)
    {
        $result = $this->definition;
        foreach ($segments as $segment) {
            if (!isset($result->$segment)) {
                return false;
            }

            $result = $result->$segment;
        }

        return true;
    }

    /**
     * @param string[] $segments
     *
     * @return stdClass Path contents
     *
     * @throws InvalidArgumentException If path does not exists.
     */
    protected function getPath(array $segments)
    {
        $result = $this->definition;
        foreach ($segments as $segment) {
            if (!isset($result->$segment)) {
                throw new InvalidArgumentException('Missing ' . $segment);
            }

            $result = $result->$segment;
        }

        return $result;
    }

    /**
     * Resolve schema references to object.
     *
     * @param stdClass $schema
     *
     * @return stdClass The same object with references replaced with definition target.
     */
    protected function resolveSchemaReferences(stdClass $schema)
    {
        $refResolver = new RefResolver(new UriRetriever());
        $refResolver->resolve($schema, $this->definitionUri);

        return $schema;
    }
}
