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
     * Middleware callbacks
     * @var (callable(Request $req, Response $res): void)[]
     */
    private $middleware = [];

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

            $callback  = $route_definition->GetMethodCallback($method);
            $variables = $route_definition->GetVariables($requestUri);

            $request = new Request($requestUri, $variables);

            foreach ($this->middleware as $middleware) {
                $middleware($request, $response);
                if ($response->HasResponse()) {
                    break;
                }

            }

            if (! $response->HasResponse()) {
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
     * Adds middleware callback
     * @param callable(Request $req, Response $res): void $callback
     * @return void
     */
    public function AddMiddleware(callable $callback): void
    {
        $this->middleware[] = $callback;
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
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddGET(string $route, callable $callback): RouteDefinition
    {
        return $this->routes->AddGET($route, $callback);
    }

    /**
     * Adds a POST route with a callback
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPOST(string $route, callable $callback): RouteDefinition
    {
        return $this->routes->AddPOST($route, $callback);
    }

    /**
     * Adds a PUT route with a callback
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPUT(string $route, callable $callback): RouteDefinition
    {
        return $this->routes->AddPUT($route, $callback);
    }

    /**
     * Adds a PATCH route with a callback
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddPATCH(string $route, callable $callback): RouteDefinition
    {
        return $this->routes->AddPATCH($route, $callback);
    }

    /**
     * Adds a DELETE route with a callback
     * @param string $route
     * @param callable(Request $req, Response $res): void $callback
     * @return RouteDefinition
     */
    public function AddDELETE(string $route, callable $callback): RouteDefinition
    {
        return $this->routes->AddDELETE($route, $callback);
    }

}
