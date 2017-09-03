<?php

namespace FR3D\SwaggerAssertions;

use FR3D\SwaggerAssertions\JsonSchema\Uri\Retrievers\FileGetContentsRetriever;
use InvalidArgumentException;
use JsonSchema\RefResolver;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
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
     * @var object
     */
    protected $definition;

    /**
     * Fetch the definition and resolve the references present in the schema.
     */
    public static function fromUri(string $definitionUri): self
    {
        $refResolver = new RefResolver((new UriRetriever())->setUriRetriever(new FileGetContentsRetriever()), new UriResolver());

        return new self($refResolver->resolve($definitionUri));
    }

    /**
     * @param stdClass $definition Swagger 2 definition with all their references resolved.
     */
    public function __construct(stdClass $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param string $path Swagger path template.
     */
    public function getMethod(string $path, string $method): stdClass
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
    public function getPathTemplates(): array
    {
        return array_keys((array) $this->definition->paths);
    }

    public function getResponseSchema(string $path, string $method, string $httpCode): stdClass
    {
        $response = $this->getResponse($path, $method, $httpCode);
        if (!isset($response->schema)) {
            return new stdClass();
        }

        $this->turnXnullableToNullType($response->schema);

        return $response->schema;
    }

    /**
     * @param string $path Swagger path template.
     *
     * @return stdClass[]
     */
    public function getResponseHeaders(string $path, string $method, string $httpCode)
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
     *
     * @return string[]
     */
    public function getResponseMediaTypes(string $path, string $method): array
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
     */
    public function hasPath(array $segments): bool
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
     */
    public function findPathInTemplates(string $requestPath, &$path, &$params = []): bool
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
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException('Missing ' . $segment);
                // @codeCoverageIgnoreEnd
            }

            $result = $result->$segment;
        }

        return $result;
    }

    /**
     * @param string $path Swagger path template.
     */
    public function getResponse(string $path, string $method, string $httpCode): stdClass
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

        return $response;
    }

    /**
     * Get the request media types for the given API operation.
     *
     * If request does not have specific media types then inherit from global API media types.
     *
     * @param string $path Swagger path template.
     *
     * @return string[]
     */
    public function getRequestMediaTypes(string $path, string $method): array
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
     *
     * @return stdClass[]
     */
    public function getRequestHeadersParameters(string $path, string $method): array
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
     *
     * @return stdClass[]
     */
    public function getRequestQueryParameters(string $path, string $method): array
    {
        $parameters = $this->getRequestParameters($path, $method);
        $parameters = $this->filterParametersObjectByLocation($parameters, 'query');
        if (empty($parameters)) {
            return [];
        }

        return $parameters;
    }

    /**
     * @param string $path Swagger path template.
     */
    public function getRequestSchema(string $path, string $method): stdClass
    {
        $parameters = $this->getRequestParameters($path, $method);
        $parameters = $this->filterParametersObjectByLocation($parameters, 'body');
        switch (count($parameters)) {
            case 0:
                return new stdClass();
            case 1:
                break;
            default:
                // @codeCoverageIgnoreStart
                throw new \DomainException('Too many body parameters. Only one is allowed');
                // @codeCoverageIgnoreEnd
        }

        $parameter = $parameters[0];
        if (!isset($parameter->schema)) {
            // @codeCoverageIgnoreStart
            throw new \DomainException('schema property is required for body parameter');
            // @codeCoverageIgnoreEnd
        }

        $this->turnXnullableToNullType($parameter->schema);

        return $parameter->schema;
    }

    /**
     * @param string $path Swagger path template.
     *
     * @return stdClass[]
     */
    public function getRequestParameters(string $path, string $method): array
    {
        $result = [];
        $pathItemParameters = [
            'paths',
            $path,
            'parameters',
        ];

        // See if there any parameters shared by all methods
        if ($this->hasPath($pathItemParameters)) {
            foreach ($this->getPath($pathItemParameters) as $parameter) {
                // Index by unique ID for later merging.
                // "A unique parameter is defined by a combination of a name and location."
                // - http://swagger.io/specification/#pathItemParameters
                $uniqueId = $parameter->name . ',' . $parameter->in;
                $result[$uniqueId] = $parameter;
            }
        }

        $method = $this->getMethod($path, $method);
        if (isset($method->parameters)) {
            foreach ($method->parameters as $parameter) {
                // Operation parameters override shared parameters
                $uniqueId = $parameter->name . ',' . $parameter->in;
                $result[$uniqueId] = $parameter;
            }
        }

        return array_values($result);
    }

    /**
     * @param stdClass[] $parameters
     *
     * @return \stdClass[]
     */
    private function filterParametersObjectByLocation(array $parameters, string $location): array
    {
        return array_values(array_filter(
            $parameters,
            function ($parameter) use ($location) {
                if (!isset($parameter->in)) {
                    // @codeCoverageIgnoreStart
                    throw new InvalidArgumentException('Missing "in" field in Parameter Object');
                    // @codeCoverageIgnoreEnd
                }

                return ($parameter->in === $location);
            }
        ));
    }

    /**
     * adds support for x-nullable extension.
     *
     * turns type into ["original_type","null"] if x-nullable : true exists
     *
     * @param $schema
     */
    private function turnXnullableToNullType(&$schema)
    {
        foreach ($schema as $key => $item) {
            if ('properties' === $key) {
                foreach ($item as $property => $definition) {
                    if (isset($definition->type)) {
                        $arr = get_object_vars($definition);

                        if (array_key_exists('x-nullable', $arr)) {
                            if ($arr['x-nullable'] == true) {
                                $definition->type = [$definition->type, 'null'];

                                if (isset($definition->enum)) {
                                    $definition->enum[] = null;
                                }

                                unset($definition->{'x-nullable'});
                            }
                        }
                    } else {
                        $this->turnXnullableToNullType($definition);
                    }
                }
            } elseif ('items' === $key or 'allOf' === $key) {
                $this->turnXnullableToNullType($item);
            }
        }
    }
}
