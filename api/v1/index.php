<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

$app = new \Slim\App;


/**
 * Get components of parser URI
 *
 * Param urlRef - urlencoded string which is the URI to parse
 * If success then return components after parse
 */
$app->get('/parser', function (Request $request, Response $response, array $args) use ($app): Response {
    $uriRef = $request->getQueryParam('uri');

    $uri = new Pshemo\UriParser\Uri();
    try {
        $components = $uri->parse($uriRef);
        $response->getBody()->write(json_encode($components));
    } catch (Exception $e) {
        return $response->withJson([
            'error'=> $e->getMessage(),
            'code'=>422
        ], 422);
    }

    return $response;
});

$app->run();
