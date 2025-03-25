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
    private $routeDefinitions = [];

    /**
     * Tries to find RouteDefinition that matches the URI
     * @param string $uri
     * @return RouteDefinition|null Found RouteDefinition, null if none found
     */
    public function FindMatchingRouteDefinition(string $uri): ?RouteDefinition
    {
        foreach ($this->routeDefinitions as $route_definition) {
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
        foreach ($routes->routeDefinitions as $route => $route_definition) {
            $uri = rtrim($routePrefix, RouteDefinition::URI_SEPARATOR) . $route;

            if (isset($this->routeDefinitions[$uri])) {
                throw new ErrorException('Trying to define a defined route \'' . $uri . '\'');
            }

            $route_definition->ChangeURI($uri);

            $this->routeDefinitions[$uri] = $route_definition;
        }
    }

    /**
     * Defines a method of a route
     * @param string $routeUri
     * @param callable(Request $req, Response $res): void $callback
     * @param string $method
     * @return RouteDefinition
     */
    private function AddRoute(string $routeUri, callable $callback, string $method): RouteDefinition
    {
        $route_definition = isset($this->routeDefinitions[$routeUri])
        ? $this->routeDefinitions[$routeUri]
        : new RouteDefinition($routeUri);

        $route_definition->DefineMethod($method, $callback);

        $this->routeDefinitions[$routeUri] = $route_definition;

        return $route_definition;
    }

    /**
     * Adds a GET route
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddGET(string $route, callable $callback): RouteDefinition
    {
        return $this->AddRoute($route, $callback, RequestMethod::GET);
    }

    /**
     * Adds a POST route
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPOST(string $route, callable $callback): RouteDefinition
    {
        return $this->AddRoute($route, $callback, RequestMethod::POST);
    }

    /**
     * Adds a PUT route
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPUT(string $route, callable $callback): RouteDefinition
    {
        return $this->AddRoute($route, $callback, RequestMethod::PUT);
    }

    /**
     * Adds a PATCH route
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPATCH(string $route, callable $callback): RouteDefinition
    {
        return $this->AddRoute($route, $callback, RequestMethod::PATCH);
    }

    /**
     * Adds a DELETE route
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddDELETE(string $route, callable $callback): RouteDefinition
    {
        return $this->AddRoute($route, $callback, RequestMethod::DELETE);
    }

}
