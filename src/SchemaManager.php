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
     * @var stdClass
     */
    protected $definition;

    /**
     * Fetch the definition and resolve the references present in the schema.
     *
     * @param string $definitionUri
     *
     * @return self
     */
    public static function fromUri($definitionUri)
    {
        $refResolver = new RefResolver((new UriRetriever())->setUriRetriever(new FileGetContentsRetriever()), new UriResolver());

        return new self($refResolver->resolve($definitionUri));
    }

    /**
     * @param object $definition Swagger 2 definition with all their references resolved.
     */
    public function __construct($definition)
    {
        $this->definition = $definition;
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
            return new stdClass();
        }

        return $response->schema;
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

        return $response;
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
     * @return stdClass[]
     */
    public function getRequestQueryParameters($path, $method)
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

        return $parameter->schema;
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
            return [];
        }

        return $method->parameters;
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
                    // @codeCoverageIgnoreStart
                    throw new InvalidArgumentException('Missing "in" field in Parameter Object');
                    // @codeCoverageIgnoreEnd
                }

                return ($parameter->in === $location);
            }
        ));
    }
}
