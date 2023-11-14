<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Request;

class Login
{
    private $users = [
        "1",
        "2",
        "3"
    ];

    function __invoke(RequestContext $requestContext, Request $request): mixed
    {
        return [
            "users" => $this->users
        ];
    }
}

return new Login();