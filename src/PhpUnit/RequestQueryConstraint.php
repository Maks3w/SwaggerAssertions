<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;

/**
 * Validate request query parameters match against defined Swagger request query schema.
 */
class RequestQueryConstraint extends JsonSchemaConstraint
{
    /**
     * @param \stdClass[] $queryParameters
     * @param Validator $validator
     */
    public function __construct($queryParameters, Validator $validator)
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

        parent::__construct($normalizedSchema, 'request query', $validator);
    }

    protected function matches($headers)
    {
        $headers = (object) $headers;

        return parent::matches($headers);
    }
}
