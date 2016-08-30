<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Constraints\Factory;

/**
 * Validate response headers match against defined Swagger response headers schema.
 */
class ResponseHeadersConstraint extends JsonSchemaConstraint
{
    /**
     * @param object $headersSchema
     * @param Factory $factory
     */
    public function __construct($headersSchema, Factory $factory = null)
    {
        $normalizedSchema = new \stdClass();
        $normalizedSchema->properties = (object) array_change_key_case((array) $headersSchema, CASE_LOWER);
        $normalizedSchema->required = array_keys((array) $normalizedSchema->properties);

        parent::__construct($normalizedSchema, 'response header', $factory);
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
