<?php

namespace ZiffDavis\Laravel\Onelogin\Middleware;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Router;

class OneloginCsrfDisablerMiddleware
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    protected $container;

    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function __invoke($request, \Closure $next)
    {
        $csrfMiddlewareClass = Arr::first($this->router->gatherRouteMiddleware($this->router->getCurrentRoute()), function ($middleware) {
            return in_array(VerifyCsrfToken::class, class_parents($middleware));
        });

        // replace the built in csrf middleware with essentially a no-op
        $this->container->extend($csrfMiddlewareClass, function () {
            return function ($request, \Closure $next) {
                return $next($request);
            };
        });

        return $next($request);
    }
}