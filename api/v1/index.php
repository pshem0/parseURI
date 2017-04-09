<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;
$uri = new Olx\UriParser\Uri();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->write("Welcome to Slimek!");
    return $response;
});


$app->get('/validate/{uriRef}', function (Request $request, Response $response) {
    $uriRef = $request->getAttribute('uriRef');
    $result = \Olx\UriParser\Uri::isValid($uriRef);
    $response->getBody()->write(json_encode($result));

    return $response;
});
$app->run();