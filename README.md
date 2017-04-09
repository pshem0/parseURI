Pshemo\UriParser
=========
Simple POC for parsing URI. Based on function parse_url() with some additions for edge cases.

Lib
-------

The library provides main methods:
1. `parse` to split URI into components and get access to them as array
2. `isValid` to validate provided URI compliance with [RFC3986](https://www.ietf.org/rfc/rfc3986.txt)

Designed as library with source code in ``/lib/``.

RESTful API
-------
Provide simple RESTful API available via GET method at:

```bash
/api/v1/parser?uri={URI}
```
The response deliver JSON with components of provided URI or 422 error code if URI is mallformed.
Used [Slim microframework](http://www.slimframework.com/) which provides router that maps route callback to specified 
HTTP method and URI and to encapsulate response in JSON format.

Example
--------
In `/public` directory you can find simple form to validate and parse URIs.
 
 Testing
 -------
 
 Parser has a [PHPUnit](https://phpunit.de) test suite. To run the tests, run the following command from the project folder.
 
 ```bash
 $ composer phpunit
 ```