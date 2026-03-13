<?php

namespace App\Router;

use Throwable;

/**
 * Router dispatches requests using stored routes and middleware
 */
class Router
{
    /**
     * All route definitions
     * @var Routes
     */
    private $routes;

    /**
     * Initializes the router
     */
    public function __construct()
    {
        $this->routes = new Routes();
    }

    /**
     * Dispatches request
     * @param string $requestUri
     * @return void
     */
    public function DispatchRequest(string $requestUri): void
    {
        $response = new Response();

        try {
            $method           = $_SERVER['REQUEST_METHOD'];
            $route_definition = $this->routes->FindMatchingRouteDefinition($requestUri);

            if ($route_definition == null) {
                throw new RequestError(404, 'server', '\'' . $requestUri . '\' not found');
            }

            if (! $route_definition->IsMethodDefined($method)) {
                throw new RequestError(405, 'server', 'Method ' . $method . ' not allowed for \'' . $requestUri . '\'');
            }

            $routeCallbacks  = $route_definition->GetMethodCallback($method);
            $variables = $route_definition->GetVariables($requestUri);

            $request = new Request($requestUri, $variables);

            if ($routeCallbacks->middleware != null) {
                $middleware = $routeCallbacks->middleware;
                $middleware($request, $response);
            }

            if (!$response->HasResponse()) {
                $callback = $routeCallbacks->callback;
                $callback($request, $response);
            }

        } catch (RequestError $error) {
            $response->SetError($error);
        } catch (Throwable $error) {
            $response->SetError(new RequestError(500, 'server', $error->getMessage()));
        }

        $response->Respond();
    }

    /**
     * Adds routes:
     * route_prefix/routes
     * @param string $routePrefix
     * @param Routes $routes
     * @return void
     */
    public function AddRoutes(string $routePrefix, Routes $routes): void
    {
        $this->routes->AddRoutes($routePrefix, $routes);
    }

    /**
     * Adds a GET route with a callback
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddGET(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->routes->AddGET($route, $callback, $middleware);
    }

    /**
     * Adds a POST route with a callback
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPOST(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->routes->AddPOST($route, $callback, $middleware);
    }

    /**
     * Adds a PUT route with a callback
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPUT(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->routes->AddPUT($route, $callback, $middleware);
    }

    /**
     * Adds a PATCH route with a callback
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddPATCH(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->routes->AddPATCH($route, $callback, $middleware);
    }

    /**
     * Adds a DELETE route with a callback
     * @param string $route
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @return RouteDefinition
     */
    public function AddDELETE(string $route, \Closure $callback, ?\Closure $middleware = null): RouteDefinition
    {
        return $this->routes->AddDELETE($route, $callback, $middleware);
    }

}
