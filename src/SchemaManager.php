<?php

namespace FR3D\SwaggerAssertions;

use FR3D\SwaggerAssertions\JsonSchema\Uri\UriRetriever;
use InvalidArgumentException;
use JsonSchema\RefResolver;
use Rize\UriTemplate\UriTemplate;
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
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return stdClass
     */
    public function getMethod($path, $method)
    {
        $method = strtolower($method);
        $pathSegments = function ($path, $method) {
            return [
                'paths',
                $path,
                $method,
            ];
        };

        $method = $this->getPath($pathSegments($path, $method));

        return $method;
    }

    /**
     * @return string[]
     */
    public function getPathTemplates()
    {
        return array_keys((array) $this->definition->paths);
    }

    /**
     * @param string $path Swagger path template.
     * @param string $method
     * @param string $httpCode
     *
     * @return stdClass
     */
    public function getResponseSchema($path, $method, $httpCode)
    {
        $response = $this->getResponse($path, $method, $httpCode);
        if (!isset($response->schema)) {
            throw new \UnexpectedValueException(
                'Missing schema definition for ' . $this->pathToString([$path, $method, $httpCode])
            );
        }

        $schema = $response->schema;

        return $this->resolveSchemaReferences($schema);
    }

    /**
     * @param string $path Swagger path template.
     * @param string $method
     * @param string $httpCode
     *
     * @return stdClass[]
     */
    public function getResponseHeaders($path, $method, $httpCode)
    {
        $response = $this->getResponse($path, $method, $httpCode);
        if (!isset($response->headers)) {
            return [];
        }

        $headers = $response->headers;

        return $headers;
    }

    /**
     * Get the response media types for the given API operation.
     *
     * If response does not have specific media types then inherit from global API media types.
     *
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return string[]
     */
    public function getResponseMediaTypes($path, $method)
    {
        $method = strtolower($method);
        $responseMediaTypes = [
            'paths',
            $path,
            $method,
            'produces',
        ];

        if ($this->hasPath($responseMediaTypes)) {
            $mediaTypes = $this->getPath($responseMediaTypes);
        } else {
            $mediaTypes = $this->getPath(['produces']);
        }

        return $mediaTypes;
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
     * @param string $requestPath percent-encoded path used on the request.
     * @param string $path Output variable. matched path
     * @param array $params Output variable. path parameters
     *
     * @return bool
     */
    public function findPathInTemplates($requestPath, &$path, &$params = [])
    {
        $uriTemplateManager = new UriTemplate();
        foreach ($this->getPathTemplates() as $template) {
            if (isset($this->definition->basePath)) {
                $fullTemplate = $this->definition->basePath . $template;
            } else {
                $fullTemplate = $template;
            }

            $params = $uriTemplateManager->extract($fullTemplate, $requestPath, true);
            if ($params !== null) {
                $path = $template;

                // Swagger don't follow RFC6570 so array parameters must be treated like a single string argument.
                array_walk($params, function (&$param) {
                    if (is_array($param)) {
                        $param = implode(',', $param);
                    }
                });

                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $segments
     *
     * @return mixed Path contents
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

    /**
     * @param string $path Swagger path template.
     * @param string $method
     * @param int $httpCode
     *
     * @return stdClass
     */
    public function getResponse($path, $method, $httpCode)
    {
        $method = strtolower($method);
        $pathSegments = function ($path, $method, $httpCode) {
            return [
                'paths',
                $path,
                $method,
                'responses',
                $httpCode,
            ];
        };

        if ($this->hasPath($pathSegments($path, $method, $httpCode))) {
            $response = $this->getPath($pathSegments($path, $method, $httpCode));
        } else {
            $response = $this->getPath($pathSegments($path, $method, 'default'));
        }

        return $this->resolveSchemaReferences($response);
    }

    /**
     * Get the request media types for the given API operation.
     *
     * If request does not have specific media types then inherit from global API media types.
     *
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return string[]
     */
    public function getRequestMediaTypes($path, $method)
    {
        $method = strtolower($method);
        $mediaTypesPath = [
            'paths',
            $path,
            $method,
            'consumes',
        ];

        if ($this->hasPath($mediaTypesPath)) {
            $mediaTypes = $this->getPath($mediaTypesPath);
        } else {
            $mediaTypes = $this->getPath(['consumes']);
        }

        return $mediaTypes;
    }

    /**
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return stdClass[]
     */
    public function getRequestHeadersParameters($path, $method)
    {
        $parameters = $this->getRequestParameters($path, $method);
        $parameters = $this->filterParametersObjectByLocation($parameters, 'header');
        if (empty($parameters)) {
            return [];
        }

        return $parameters;
    }

    /**
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return stdClass
     */
    public function getRequestSchema($path, $method)
    {
        $parameters = $this->getRequestParameters($path, $method);
        $parameters = $this->filterParametersObjectByLocation($parameters, 'body');
        switch (count($parameters)) {
            case 0:
                return new stdClass();
            case 1:
                break;
            default:
                throw new \DomainException('Too much body parameters. Only one is allowed');
        }

        $parameter = $parameters[0];
        if (!isset($parameter->schema)) {
            throw new \DomainException('schema property is required for body parameter');
        }

        return $this->resolveSchemaReferences($parameter->schema);
    }

    /**
     * @param string $path Swagger path template.
     * @param string $method
     *
     * @return stdClass[]
     */
    public function getRequestParameters($path, $method)
    {
        $method = $this->getMethod($path, $method);
        if (!isset($method->parameters)) {
            throw new InvalidArgumentException('Missing Parameter Object');
        }

        $parameters = $method->parameters;

        array_walk($parameters, [$this, 'resolveSchemaReferences']);

        return $parameters;
    }

    /**
     * @param array $path
     *
     * @return string
     */
    public function pathToString(array $path)
    {
        return implode('.', $path);
    }

    /**
     * @param stdClass[] $parameters
     * @param string $location
     *
     * @return \stdClass[]
     */
    private function filterParametersObjectByLocation(array $parameters, $location)
    {
        return array_values(array_filter(
            $parameters,
            function ($parameter) use ($location) {
                if (!isset($parameter->in)) {
                    throw new InvalidArgumentException('Missing "in" field in Parameter Object');
                }

                return ($parameter->in === $location);
            }
        ));
    }
}
