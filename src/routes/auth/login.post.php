<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

return function (RequestContext $requestContext, Request $request) {
    dump($_GET["id"]);
}
    ?>