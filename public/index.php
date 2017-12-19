<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/
require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;


$app = new Kernel('dev', true);
$loop = Factory::create();
$request = new HttpFoundationFactory();


$server = new React\Http\Server(function (ServerRequestInterface $reactRequest) use ($app, $request) {
    $symfonyRequest = $request->createRequest($reactRequest);
    $response = $app->handle($symfonyRequest);

    return new Response($response->getStatusCode(), $response->headers->all(), $response->getContent());
});


$socket = new React\Socket\Server(8080, $loop);
$server->listen($socket);
$loop->run();
