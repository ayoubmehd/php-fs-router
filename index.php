<?php

require_once("./vendor/autoload.php");

use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;

function dump($var)
{
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

function dd($var)
{
    dump($var);
    die();
}

define('ROOT', __DIR__);
define('SRC', ROOT . "/src");
define('ROUTES', SRC . '/routes');

$finder = Finder::create();

$finder
    ->files()
    ->name("*.php")
    ->in(ROUTES);

$routes = new RouteCollection();


$request = Request::createFromGlobals();
$requestContext = new RequestContext();

$allowedMethod = [
    "GET",
    "POST"
];

foreach ($finder as $file) {
    $fullPath = "/" . \str_replace(".php", "", $file->getRelativePathname());

    $elements = explode("/", $fullPath);

    $fileName = end($elements);

    list($path, $httpMethod) = array_pad(explode(".", $fileName), 2, "get");

    $httpPath = str_replace("index", "", str_replace("." . $httpMethod, "", $fullPath));

    $httpMethod = strtoupper($httpMethod);

    if (in_array($httpMethod, $allowedMethod)) {
        $route = new Route($httpPath, [
            "_controller" => function () use ($file, $request, $requestContext) {
                $maybeFunction = require_once(ROUTES . "/" . $file->getRelativePathname());

                if (is_callable($maybeFunction)) {
                    $data = $maybeFunction($requestContext, $request);

                    extract($data);

                    $viewFile = ROUTES . "/" . str_replace(".php", ".view.php", $file->getRelativePathname());

                    if (file_exists($viewFile)) {
                        require_once($viewFile);
                    }
                }

            }
        ]);

        $route->setMethods($httpMethod);
        $routes->add(str_replace("/", ".", $fullPath), $route);
    }
}

$requestContext->fromRequest($request);

$matcher = new UrlMatcher($routes, $requestContext);

$parms = $matcher->match($request->getPathInfo());

$response = call_user_func($parms["_controller"]);
$response->send();