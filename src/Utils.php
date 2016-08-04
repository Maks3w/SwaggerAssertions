<?php

namespace FR3D\SwaggerAssertions;

/**
 * Static helpers functions.
 */
class Utils
{
    /**
     * Converts an xml value to a PHP type.
     *
     * Extracted from Symfony XmlUtils class
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function phpize($value)
    {
        if (is_array($value)) {
            $normalizedValue = [];

            foreach ($value as $key => $item) {
                $normalizedValue[$key] = static::phpize($item);
            }

            return $normalizedValue;
        }

        $value = (string) $value;
        $lowercaseValue = strtolower($value);

        switch (true) {
            case 'null' === $lowercaseValue:
                return;
            case ctype_digit($value):
                $raw = $value;
                $cast = (int) $value;

                return '0' == $value[0] ? octdec($value) : (((string) $raw === (string) $cast) ? $cast : $raw);
            case isset($value[1]) && '-' === $value[0] && ctype_digit(substr($value, 1)):
                $raw = $value;
                $cast = (int) $value;

                return '0' == $value[1] ? octdec($value) : (((string) $raw === (string) $cast) ? $cast : $raw);
            case 'true' === $lowercaseValue:
                return true;
            case 'false' === $lowercaseValue:
                return false;
            case isset($value[1]) && '0b' == $value[0] . $value[1]:
                return bindec($value);
            case is_numeric($value):
                return '0x' === $value[0] . $value[1] ? hexdec($value) : (float) $value;
            case preg_match('/^0x[0-9a-f]++$/i', $value):
                return hexdec($value);
            case preg_match('/^(-|\+)?[0-9]+(\.[0-9]+)?$/', $value):
                return (float) $value;
            default:
                return $value;
        }
    }
}
