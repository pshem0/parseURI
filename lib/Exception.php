<?php

declare(strict_types=1);

namespace Olx\UriParser;

use InvalidArgumentException;

/**
 *
 * @see https://tools.ietf.org/html/rfc3986
 */
class Exception extends InvalidArgumentException
{
    /**
     * Malformed URI
     *
     * Thrown when URI is not compliant with RFC3896.
     *
     * @param string $uri URI provided to get more specific message
     * @return static
     */
    public static function malformedURI(string $uri)
    {
        return new static(sprintf('The submitted uri `%s` is incorrectly built semantically (RFC 3986)', $uri));
    }

    /**
     * Parse of URI failed.
     *
     * Thrown when parse_url() function were unable to parse provided URL(or even URI)
     *
     * @param string $uri URI provided to get more specific message
     * @return static
     */
    public static function unableToParseURI(string $uri)
    {
        return new static(sprintf('Unable to parse uri `%s`', $uri));
    }
}
