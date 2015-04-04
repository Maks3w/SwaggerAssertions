<?php

namespace FR3D\SwaggerAssertions\JsonSchema\Uri;

use JsonSchema\Uri\UriRetriever as BaseUriRetriever;

class UriRetriever extends BaseUriRetriever
{
    /**
     * Workaround https://github.com/justinrainbow/json-schema/issues/130.
     */
    public function confirmMediaType($uriRetriever, $uri)
    {
        return true;
    }
}
