<?php

namespace FR3D\SwaggerAssertions\JsonSchema\Constraints;

use JsonSchema\Constraints\FormatConstraint as BaseFormatConstraint;

class FormatConstraint extends BaseFormatConstraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        // Workaround https://github.com/justinrainbow/json-schema/pull/125
        if (!isset($schema->format)) {
            return;
        }

        switch ($schema->format) {
            case 'date':
                if (!$date = $this->validateDateTime($element, 'Y-m-d')) {
                    $this->addError(
                        $path,
                        sprintf('Invalid date %s, expected format YYYY-MM-DD', json_encode($element))
                    )
                    ;
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $this->addError($path, sprintf('Invalid time %s, expected format hh:mm:ss', json_encode($element)));
                }
                break;

            case 'date-time':
                if (!$this->validateDateTime($element, 'Y-m-d\TH:i:s\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:s.u\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sP') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sO')
                ) {
                    $this->addError(
                        $path,
                        sprintf(
                            'Invalid date-time %s, expected format YYYY-MM-DDThh:mm:ssZ or YYYY-MM-DDThh:mm:ss+hh:mm',
                            json_encode($element)
                        )
                    )
                    ;
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $this->addError(
                        $path,
                        sprintf(
                            'Invalid time %s, expected integer of milliseconds since Epoch',
                            json_encode($element)
                        )
                    )
                    ;
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element)) {
                    $this->addError($path, 'Invalid regex format ' . $element);
                }
                break;

            case 'color':
                if (!$this->validateColor($element)) {
                    $this->addError($path, 'Invalid color');
                }
                break;

            case 'style':
                if (!$this->validateStyle($element)) {
                    $this->addError($path, 'Invalid style');
                }
                break;

            case 'phone':
                if (!$this->validatePhone($element)) {
                    $this->addError($path, 'Invalid phone number');
                }
                break;

            case 'uri':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, 'Invalid URL format');
                }
                break;

            case 'email':
                if (null === filter_var($element, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, 'Invalid email');
                }
                break;

            case 'ip-address':
            case 'ipv4':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4)) {
                    $this->addError($path, 'Invalid IP address');
                }
                break;

            case 'ipv6':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6)) {
                    $this->addError($path, 'Invalid IP address');
                }
                break;

            case 'host-name':
            case 'hostname':
                if (!$this->validateHostname($element)) {
                    $this->addError($path, 'Invalid hostname');
                }
                break;

            default:
                break;
        }
    }
}
