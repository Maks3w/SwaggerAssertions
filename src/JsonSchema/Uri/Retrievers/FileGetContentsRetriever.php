<?php

namespace FR3D\SwaggerAssertions\JsonSchema\Uri\Retrievers;

use JsonSchema\Exception\ResourceNotFoundException;
use JsonSchema\Uri\Retrievers\FileGetContents;

/**
 * Workaround to BC Break https://github.com/justinrainbow/json-schema/pull/262
 */
class FileGetContentsRetriever extends FileGetContents
{
    public function retrieve($uri)
    {
        set_error_handler(function () use ($uri) {
            throw new ResourceNotFoundException('JSON schema not found at ' . $uri);
        });
        $response = file_get_contents($uri);
        restore_error_handler();

        if (false === $response) {
            throw new ResourceNotFoundException('JSON schema was not retrieved at ' . $uri);
        }

        if ($response == '' && substr($uri, 0, 7) == 'file://' && substr($uri, -1) == '/') {
            throw new ResourceNotFoundException('JSON schema not found at ' . $uri);
        }

        $this->messageBody = $response;
        if (!empty($http_response_header)) {
            $this->fetchContentType($http_response_header);
        } else {
            // Could be a "file://" url or something else - fake up the response
            $this->contentType = null;
        }

        return $this->messageBody;
    }

    /**
     * @param array $headers HTTP Response Headers
     *
     * @return boolean Whether the Content-Type header was found or not
     */
    private function fetchContentType(array $headers)
    {
        foreach ($headers as $header) {
            if ($this->contentType = self::getContentTypeMatchInHeader($header)) {
                return true;
            }
        }

        return false;
    }
}
