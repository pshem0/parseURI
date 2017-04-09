<?php
declare(strict_types=1);

namespace Pshemo\UriParser;

use Pshemo\UriParser\Uri;
use Pshemo\UriParser\Exception;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private $uriParser;

    protected function setUp()
    {
        $this->uriParser = new Uri();
    }

    /**
     * Test successful parse of correct URIs.
     *
     * @dataProvider dataValidURI
     * @param $uri
     * @param $expected
     */
    public function testParseSucceed(string $uri, array $expected)
    {
        $result = $this->uriParser->parse($uri);
        $this->assertSame($expected, $result, 'Parse failed for URI: '.$uri);
    }

    /**
     * Set of correct URIs. Based on unittest provided by thephpleague.com in the URI package.
     *
     * @covers Uri::isValid
     */
    public function dataValidURI()
    {
        return [
            'complete URI' => [
                'scheme://user:pass@host:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'host',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI is not normalized' => [
                'ScheMe://user:pass@HoSt:81/path?query#fragment',
                [
                    'scheme' => 'ScheMe',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without scheme' => [
                '//user:pass@HoSt:81/path?query#fragment',
                [
                    'scheme' => null,
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without empty authority only' => [
                '//',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => '',
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI without userinfo' => [
                'scheme://HoSt:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty userinfo' => [
                'scheme://@HoSt:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => '',
                    'pass' => null,
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without port' => [
                'scheme://user:pass@host/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'host',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI with an empty port' => [
                'scheme://user:pass@host:/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'host',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without user info and port' => [
                'scheme://host/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => 'host',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI with host IP' => [
                'scheme://10.0.0.2/p?q#f',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => '10.0.0.2',
                    'port' => null,
                    'path' => '/p',
                    'query' => 'q',
                    'fragment' => 'f',
                ],
            ],
            'URI without authority' => [
                'scheme:path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without authority and scheme' => [
                '/path',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI without path' => [
                'scheme://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                    'port' => null,
                    'path' => null,
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without path and scheme' => [
                '//[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]?query#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                    'port' => null,
                    'path' => null,
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without scheme with IPv6 host and port' => [
                '//[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:42?query#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                    'port' => 42,
                    'path' => null,
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'complete URI without scheme' => [
                '//user@[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:42?q#f',
                [
                    'scheme' => null,
                    'user' => 'user',
                    'pass' => null,
                    'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                    'port' => 42,
                    'path' => null,
                    'query' => 'q',
                    'fragment' => 'f',
                ],
            ],
            'URI without authority and query' => [
                'scheme:path#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty query' => [
                'scheme:path?#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI with query only' => [
                '?query',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => 'query',
                    'fragment' => null,
                ],
            ],
            'URN' => [
                'urn:isbn:9876543210',
                [
                    'scheme' => 'urn',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'isbn:9876543210',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with empty fragment' => [
                'scheme:path#',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with fragment only' => [
                '#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty fragment only' => [
                '#',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI without authority 2' => [
                'path#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty query and fragment' => [
                '?#',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with absolute path' => [
                '/?#',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with absolute authority' => [
                'https://example.com./p?#f',
                [
                    'scheme' => 'https',
                    'user' => null,
                    'pass' => null,
                    'host' => 'example.com.',
                    'port' => null,
                    'path' => '/p',
                    'query' => null,
                    'fragment' => 'f',
                ],
            ],
            'URI with absolute path only' => [
                '/',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with empty query only' => [
                '?',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'relative path' => [
                '../relative/path',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '../relative/path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'complex authority' => [
                'http://a_.!~*\'(-)n0123Di%25%26:pass;:&=+$,word@www.zend.com',
                [
                    'scheme' => 'http',
                    'user' => 'a_.!~*\'(-)n0123Di%25%26',
                    'pass' => 'pass;:&=+$,word',
                    'host' => 'www.zend.com',
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'complex authority without scheme' => [
                '//a_.!~*\'(-)n0123Di%25%26:pass;:&=+$,word@www.zend.com',
                [
                    'scheme' => null,
                    'user' => 'a_.!~*\'(-)n0123Di%25%26',
                    'pass' => 'pass;:&=+$,word',
                    'host' => 'www.zend.com',
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'single word is a path' => [
                'http',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'http',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'single word is a path, no' => [
                'http:::/path',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '::/path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'fragment with pseudo segment' => [
                'http://example.com#foo=1/bar=2',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => 'example.com',
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => 'foo=1/bar=2',
                ],
            ],
            'empty string' => [
                '',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'scheme only URI' => [
                'http:',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => null,
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'RFC3986 LDAP example' => [
                'ldap://[2001:db8::7]/c=GB?objectClass?one',
                [
                    'scheme' => 'ldap',
                    'user' => null,
                    'pass' => null,
                    'host' => '[2001:db8::7]',
                    'port' => null,
                    'path' => '/c=GB',
                    'query' => 'objectClass?one',
                    'fragment' => null,
                ],
            ],
            'colon detection respect RFC3986 (1)' => [
                'http://example.org/hello:12?foo=bar#test',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => 'example.org',
                    'port' => null,
                    'path' => '/hello:12',
                    'query' => 'foo=bar',
                    'fragment' => 'test',
                ],
            ],
            'colon detection respect RFC3986 (2)' => [
                '/path/to/colon:34',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/path/to/colon:34',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
        ];
    }

    /**
     * Test of failed verification for semanticaly wrong URIs.
     * @see RFC3986 https://tools.ietf.org/html/rfc3986#section-3.1
     * @dataProvider dataInvalidURI
     * @param string $uri
     *
     * @expectedException Pshemo\UriParser\Exception
     */
    public function testParseFailed(string $uri)
    {
        $this->uriParser->parse($uri);
    }

    /**
     * Set of malformed URIs.
     */
    public function dataInvalidURI()
    {
        return [
            'invalid uri with incomplete scheme' => ['://host:80/p?q#f'],
            'invalid port' => ['//host:port/path?query#fragment'],
            'invalid host' => ['scheme://[127.0.0.1]/path?query#fragment'],
            'invalid ipv6 scoped 1' => ['scheme://[::1%25%23]/path?query#fragment'],
            'invalid ipv6 scoped 2' => ['scheme://[fe80::1234::%251]/path?query#fragment'],
            'invalid ipv6 host 1' => ['scheme://[::1]./path?query#fragment'],
            'invalid ipv6 host 2' => ['scheme://[[::1]]:80/path?query#fragment'],
            'invalid char on URI' => ["scheme://host/path/\r\n/toto"],
            'invalid path only URI' => ['2620:0:1cfe:face:b00c::3'],
            'invalid scheme and path' => ['0scheme://host/path?query#fragment'],
            'invalid path PHP' => ['[::1]:80']
        ];
    }
}
