<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use JsonSchema\Validator;

/**
 * Validate response headers match against defined Swagger response headers schema.
 */
class ResponseHeadersConstraint extends JsonSchemaConstraint
{
    public function __construct($headersSchema, Validator $validator)
    {
        $normalizedSchema = new \stdClass();
        $normalizedSchema->properties = (object) array_change_key_case((array) $headersSchema, CASE_LOWER);
        $normalizedSchema->required = array_keys((array) $normalizedSchema->properties);

        parent::__construct($normalizedSchema, 'response header', $validator);
    }

    protected function matches($headers): bool
    {
        $headers = (object) array_change_key_case((array) $headers, CASE_LOWER);

        return parent::matches($headers);
    }
}
