<?php

namespace App\Services\Router;

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
     * All error event callbacks
     * @var (\Closure(RouterError $error): void)[]
     */
    private $error_events = [];

    /**
     * Debug mode returns more information about errors to the API user
     * @var bool
     */
    private $debug_mode;

    /**
     * Initializes the router.
     * @param $debug_mode Debug mode returns more information about errors to the API user
     */
    public function __construct(bool $debug_mode = false)
    {
        $this->routes = new Routes();
        $this->debug_mode = $debug_mode;
    }

    /**
     * Calls all error callbacks and makes a new error response
     * @param RouterError $error
     * @return void
     */
    private function RespondWithError(RouterError $error): void
    {
        /**
         * @var Throwable[]
         */
        $new_errors = [];

        foreach ($this->error_events as $on_error) {
            try {
                $on_error($error);
            } catch (Throwable $e) {
                $new_errors[] = $e;
            }
        }

        $message = $error->getMessage();

        if ($this->debug_mode) {
            $message .= ' | ' . $error->GetDevMessage();

            foreach ($new_errors as $new_error) {
                $message .= ' | ' . $new_error->getMessage();
            }
        }

        $request_error = new RequestError($error->getCode(), 'server', $message);

        $response = new Response();
        $response->SetError($request_error);
        $response->Respond();
    }

    /**
     * Dispatches the current request uri
     * @return void
     */
    public function DispatchRequest(): void
    {
        $this->DispatchRequestWithUri(Request::GetServerRequestURI());
    }

    /**
     * Dispatches a specific request uri
     * @param string $request_uri
     * @return void
     */
    public function DispatchRequestWithUri(string $request_uri): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $this->RespondWithError(new RouterError(RouterError::TYPE_FATAL, 500, 'Internal error', $error['message'], $error));
            }
        });

        header("Access-Control-Allow-Origin: *", true);
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS", true);
        header("Access-Control-Allow-Headers: Content-Type, Authorization", true);

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $response = new Response();

        try {
            $route_definition = $this->routes->FindMatchingRouteDefinition($request_uri);

            if ($route_definition == null) {
                throw new RouterError(RouterError::TYPE_ACCESS, 404, '\'' . $request_uri . '\' not found', 'No RouteDefinition matched');
            }

            if (!$route_definition->IsMethodDefined($method)) {
                throw new RouterError(
                    RouterError::TYPE_ACCESS,
                    405,
                    'Method ' . $method . ' not allowed for \'' . $request_uri . '\'',
                    'Defined methods: ' . join(', ', $route_definition->GetDefinedMethods())
                );
            }

            $route_callbacks  = $route_definition->GetMethodCallback($method);
            $variables = $route_definition->GetVariables($request_uri);

            $request = new Request($request_uri, $variables);

            if ($route_callbacks->middleware != null) {
                $middleware = $route_callbacks->middleware;
                $middleware($request, $response);
            }

            if (!$response->HasResponse()) {
                $callback = $route_callbacks->callback;
                $callback($request, $response);
            }

        } catch (RouterError $error) {
            $this->RespondWithError($error);
        } catch (RequestError $error) {
            $response->SetError($error);
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

    /**
     * Adds an error event callback
     * @param \Closure(RouterError $error): void $callback
     * @return void
     */
    public function OnError(\Closure $callback): void
    {
        $this->error_events[] = $callback;
    }
}
