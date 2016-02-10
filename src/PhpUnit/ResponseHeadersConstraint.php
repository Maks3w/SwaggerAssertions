<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

/**
 * Validate response headers match against defined Swagger response headers schema.
 */
class ResponseHeadersConstraint extends JsonSchemaConstraint
{
    /**
     * @param object $headersSchema
     */
    public function __construct($headersSchema)
    {
        $normalizedSchema = new \stdClass();
        $normalizedSchema->properties = (object) array_change_key_case((array) $headersSchema, CASE_LOWER);
        $normalizedSchema->required = array_keys((array) $normalizedSchema->properties);

        parent::__construct($normalizedSchema, 'response header');
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
