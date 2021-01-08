<?php

declare(strict_types=1);

namespace FR3D\SwaggerAssertions\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;

/**
 * Validate given media type is present in the allowed media type collection.
 */
class MediaTypeConstraint extends Constraint
{
    /**
     * @var string[]
     */
    protected $allowedMediaTypes;

    /**
     * @param string[] $allowedMediaTypes Collection of allowed media types.
     */
    public function __construct(array $allowedMediaTypes)
    {
        $this->allowedMediaTypes = $allowedMediaTypes;
    }

    protected function matches($other): bool
    {
        return in_array($other, $this->allowedMediaTypes, true);
    }

    public function toString(): string
    {
        return 'is an allowed media type (' . implode(', ', $this->allowedMediaTypes) . ')';
    }
}
