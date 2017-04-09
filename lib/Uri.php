<?php

declare(strict_types=1);

namespace Pshemo\UriParser;

/**
 * Provide parse solution to Uri.
 *
 * Additionally verify if the URI is compliant with RFC3986, but does not provide any additional validation
 * based on the URI scheme.
 * Provides separated method to get every single component of parsed URI.
 *
 * @see URI in Wikipedia https://en.wikipedia.org/wiki/Uniform_Resource_Identifier
 * @see RFC3986 https://tools.ietf.org/html/rfc3986
 */
class Uri
{
    public $uriComponents = [
        'scheme' => null,
        'user' => null,
        'pass' => null,
        'host' => null,
        'port' => null,
        'path' => null,
        'query' => null,
        'fragment' => null
    ];

    /**
     * Parse URI using PHP built-in function parse_url().
     * Note: it's not fully compliant to RFC specification and formerly
     * is limited to URLs but gives proper results also for URI (i.e. URN)
     *
     * @param string $uri
     * @param bool $checkValid URI have to be validate or not before parse. Default: validate
     *
     * @return array Array of parsed components
     * @throws InvalidArgumentException if mallformed URI or parse_url() were unable to parse URI
     */
    public function parse(string $uri, bool $checkValid = true): array
    {
        // in case of 'specific' URI
        if ('' == $uri) {
            return $this->uriComponents;
        }

        if ('/' == $uri) {
            $this->uriComponents['path'] = '/';

            return $this->uriComponents;
        }

        if ('//' == $uri) {
            $this->uriComponents['host'] = '';

            return $this->uriComponents;
        }

        // check if provided URI is valid semantically
        if (true === $checkValid && !self::isValid($uri)) {
            throw Exception::malformedURI($uri);
        }

        // URI with empty scheme or host are valid. Provide placeholder to be able get components from parse_url()
        $toUnset = array();

        if ('//' === substr($uri, 0, 2)) {
            $toUnset[] = 'scheme';
            $uri = 'placeholder:' . $uri;
        } elseif ('/' === substr($uri, 0, 1)) {
            $toUnset[] = 'scheme';
            $toUnset[] = 'host';
            $uri = 'placeholder://placeholder' . $uri;
        }
        //@TODO: instead of simple parse_url() provide method fully compliant with RFC specification
        $parts = @parse_url($uri);

        if (false === $parts) {
            throw Exception::unableToParseURI($uri);
        }
        // Remove the placeholder values.
        foreach ($toUnset as $key) {
            unset($parts[$key]);
        }

        foreach ($parts as $propertyName => $value) {
            $this->uriComponents[$propertyName] = $value;
        }

        //Use available filters for parts of decomposed URI
        //@TODO: provide additional filters depending of the schema type
        $this->filterPort();

        return $this->uriComponents;
    }

    /**
     * Check if the URI string is valid.
     *
     * @param string $uri The URI to validate
     *
     * @return bool True if the string is valid, false if not
     *
     * @see http://jmrware.com/articles/2009/uri_regexp/URI_regex.html
     */
    public static function isValid(string $uri): bool
    {
        // Regular expression by http://jmrware.com/articles/2009/uri_regexp/URI_regex.html
        $pattern = '< ^
            # RFC-3986 URI component: URI-reference
            (?:                                                               # (
              [A-Za-z][A-Za-z0-9+\-.]* :                                      # URI
              (?: //
                (?: (?:[A-Za-z0-9\-._~!$&\'()*+,;=:]|%[0-9A-Fa-f]{2})* @)?
                (?:
                  \[
                  (?:
                    (?:
                      (?:                                                    (?:[0-9A-Fa-f]{1,4}:){6}
                      |                                                   :: (?:[0-9A-Fa-f]{1,4}:){5}
                      | (?:                            [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){4}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,1} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){3}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,2} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){2}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,3} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}:
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,4} [0-9A-Fa-f]{1,4})? ::
                      ) (?:
                          [0-9A-Fa-f]{1,4} : [0-9A-Fa-f]{1,4}
                        | (?: (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?) \.){3}
                              (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                        )
                    |   (?: (?:[0-9A-Fa-f]{1,4}:){0,5} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}
                    |   (?: (?:[0-9A-Fa-f]{1,4}:){0,6} [0-9A-Fa-f]{1,4})? ::
                    )
                  | [Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&\'()*+,;=:]+
                  )
                  \]
                | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
                     (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                | (?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-Fa-f]{2})*
                )
                (?: : [0-9]* )?
                (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
              | /
                (?:    (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})+
                  (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
                )?
              |        (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})+
                  (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
              |
              )
              (?:\? (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?
              (?:\# (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?
            | (?: //                                                          # / relative-ref
                (?: (?:[A-Za-z0-9\-._~!$&\'()*+,;=:]|%[0-9A-Fa-f]{2})* @)?
                (?:
                  \[
                  (?:
                    (?:
                      (?:                                                    (?:[0-9A-Fa-f]{1,4}:){6}
                      |                                                   :: (?:[0-9A-Fa-f]{1,4}:){5}
                      | (?:                            [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){4}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,1} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){3}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,2} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){2}
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,3} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}:
                      | (?: (?:[0-9A-Fa-f]{1,4}:){0,4} [0-9A-Fa-f]{1,4})? ::
                      ) (?:
                          [0-9A-Fa-f]{1,4} : [0-9A-Fa-f]{1,4}
                        | (?: (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?) \.){3}
                              (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                        )
                    |   (?: (?:[0-9A-Fa-f]{1,4}:){0,5} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}
                    |   (?: (?:[0-9A-Fa-f]{1,4}:){0,6} [0-9A-Fa-f]{1,4})? ::
                    )
                  | [Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&\'()*+,;=:]+
                  )
                  \]
                | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
                     (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                | (?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-Fa-f]{2})*
                )
                (?: : [0-9]* )?
                (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
              | /
                (?:    (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})+
                  (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
                )?
              |        (?:[A-Za-z0-9\-._~!$&\'()*+,;=@] |%[0-9A-Fa-f]{2})+
                  (?:/ (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
              |
              )
              (?:\? (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?
              (?:\# (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?
            )                                                                       # )
            $ >Sx';

        if (preg_match($pattern, $uri)) {
            return true;
        }

        return false;
    }

    /**
     * Filter port component to get value specific validator (port in available range)
     */
    private function filterPort()
    {
        $filteredPort = filter_var($this->uriComponents['port'], FILTER_VALIDATE_INT, ['options' => [
            'min_range' => 1,
            'max_range' => 65535,
        ]]);
        if ($filteredPort) {
            $this->uriComponents['port'] = $filteredPort;
        }
    }
}
