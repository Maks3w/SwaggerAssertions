<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Constraints\Factory;

/**
 * Validate request query parameters match against defined Swagger request query schema.
 */
class RequestQueryConstraint extends JsonSchemaConstraint
{
    /**
     * @param \stdClass[] $queryParameters
     * @param Factory $factory
     */
    public function __construct($queryParameters, Factory $factory = null)
    {
        $normalizedSchema = new \stdClass();
        $normalizedSchema->required = [];
        foreach ($queryParameters as $queryParameter) {
            $queryParameter = clone $queryParameter;

            if (!isset($queryParameter->name)) {
                // @codeCoverageIgnoreStart
                throw new \DomainException('Expected missing name field');
                // @codeCoverageIgnoreEnd
            }

            $normalizedName = $queryParameter->name;
            unset($queryParameter->name);

            if (isset($queryParameter->required) && $queryParameter->required) {
                $normalizedSchema->required[] = $normalizedName;
                unset($queryParameter->required);
            }

            $normalizedSchema->{$normalizedName} = $queryParameter;
        }

        parent::__construct($normalizedSchema, 'request query', $factory);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidator($headers)
    {
        $headers = (object) array_change_key_case((array) $headers, CASE_LOWER);

        return parent::getValidator($headers);
    }
}
