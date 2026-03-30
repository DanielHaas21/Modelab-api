<?php

namespace App\Router;

use ErrorException;

/**
 * Route definitions
 */
class Routes
{
    /**
     * Routes defined
     * @var RouteDefinition[]
     */
    private $route_definitions = [];

    /**
     * Tries to find RouteDefinition that matches the URI
     * @param string $uri
     * @return RouteDefinition|null Found RouteDefinition, null if none found
     */
    public function FindMatchingRouteDefinition(string $uri): ?RouteDefinition
    {
        foreach ($this->route_definitions as $route_definition) {
            if ($route_definition->MatchesWithURI($uri)) {
                return $route_definition;
            }

        }
        return null;
    }

    /**
     * Adds all routes from other routes and adds a prefix to them
     * @param string $routePrefix
     * @param Routes $routes
     * @throws \ErrorException If the routes overlap already defined routes
     * @return void
     */
    public function AddRoutes(string $routePrefix, Routes $routes): void
    {
        foreach ($routes->route_definitions as $route => $route_definition) {
            $uri = rtrim($routePrefix, RouteDefinition::URI_SEPARATOR) . $route;

            if (isset($this->route_definitions[$uri])) {
                throw new ErrorException('Trying to define a defined route \'' . $uri . '\'');
            }

            $route_definition->ChangeURI($uri);

            $this->route_definitions[$uri] = $route_definition;
        }
    }

    /**
     * Defines a method of a route
     * @param string $routeUri
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @param string $method
     * @return RouteDefinition
     */
    private function AddRoute(string $routeUri, \Closure $callback, ?\Closure $middleware, string $method): RouteDefinition
    {
        $route_definition = isset($this->route_definitions[$routeUri])
        ? $this->route_definitions[$routeUri]
        : new RouteDefinition($routeUri);

        $route_definition->DefineMethod($method, $callback, $middleware);

        $this->route_definitions[$routeUri] = $route_definition;

        return $route_definition;
    }

    /**
     * Adds a GET route
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddGET(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->AddRoute($route, $callback, $middleware, RequestMethod::GET);
    }

    /**
     * Adds a POST route
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPOST(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->AddRoute($route, $callback, $middleware, RequestMethod::POST);
    }

    /**
     * Adds a PUT route
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPUT(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->AddRoute($route, $callback, $middleware, RequestMethod::PUT);
    }

    /**
     * Adds a PATCH route
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPATCH(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->AddRoute($route, $callback, $middleware, RequestMethod::PATCH);
    }

    /**
     * Adds a DELETE route
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddDELETE(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->AddRoute($route, $callback, $middleware, RequestMethod::DELETE);
    }

}
