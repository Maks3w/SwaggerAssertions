<?php

namespace FR3D\SwaggerAssertions\PhpUnit;

/**
 * Validate request headers match against defined Swagger request headers schema.
 */
class RequestHeadersConstraint extends JsonSchemaConstraint
{
    /**
     * @param \stdClass[] $headersParameters
     */
    public function __construct($headersParameters)
    {
        $normalizedSchema = new \stdClass();
        $normalizedSchema->required = [];
        foreach ($headersParameters as $headerParameter) {
            if (!isset($headerParameter->name)) {
                // @codeCoverageIgnoreStart
                throw new \DomainException('Expected missing name field');
                // @codeCoverageIgnoreEnd
            }

            $normalizedName = strtolower($headerParameter->name);
            unset($headerParameter->name);

            if (isset($headerParameter->required) && $headerParameter->required) {
                $normalizedSchema->required[] = $normalizedName;
                unset($headerParameter->required);
            }

            $normalizedSchema->{$normalizedName} = $headerParameter;
        }

        //        $normalizedSchema->properties = (object) array_change_key_case((array) $requestSchema, CASE_LOWER);
        //        $normalizedSchema->required = array_keys((array) $normalizedSchema->properties);

        parent::__construct($normalizedSchema, 'request header');
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
