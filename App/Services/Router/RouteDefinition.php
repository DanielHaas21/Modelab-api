<?php

namespace App\Services\Router;

use ErrorException;

class RouteCallbacks
{
    /** @var Closure(Request $req, Response $res): void */
    public $callback;

    /** @var ?Closure(Request $req, Response $res): void */
    public $middleware;

    public function __construct(\Closure $callback, ?\Closure $middleware)
    {
        $this->callback = $callback;
        $this->middleware = $middleware;
    }
}

/**
 * Defines a route
 */
class RouteDefinition
{
    /**
     * Separator used in URI
     * @var string
     */
    public const URI_SEPARATOR = '/';

    /**
     * Regex of a variable in the route.
     * {var_name}
     * @var string
     */
    public const VARIABLE_REGEX = '/^\{(\S+)\}$/';

    /**
     * @var string
     */
    public const VALUE_REGEX = '(\d+)';

    /**
     * Extracts regex and variable names from the uri definition
     * @param string $uri
     * @return array{regex: string, variable_names: array}
     */
    private static function ExtractRegexAndVariableNames(string $uri): array
    {
        $route_parts = array_filter(explode(self::URI_SEPARATOR, $uri));
        $variable_names = [];

        $quoted_uri_separator = preg_quote(self::URI_SEPARATOR, self::URI_SEPARATOR);

        $route_regex = '';
        foreach ($route_parts as $route_part) {
            $route_regex .= $quoted_uri_separator;

            $variable_match_groups = [];
            $variable_match = preg_match(self::VARIABLE_REGEX, $route_part, $variable_match_groups);
            if ($variable_match) {
                $route_regex .= self::VALUE_REGEX;
                $variable_names[] = $variable_match_groups[1];
            } else {
                $route_regex .= preg_quote($route_part, self::URI_SEPARATOR);
            }

        }

        if (strlen($route_regex) == 0) {
            $route_regex = $quoted_uri_separator;
        }

        return [
            'regex' => '/^' . $route_regex . '$/',
            'variable_names' => $variable_names,
        ];
    }

    /**
     * The URI as provided
     * @var string
     */
    private $uri_definition;
    /**
     * Array of supported methods
     * @var array<string, RouteCallbacks>
     */
    private $methods;

    /**
     * Regex for matching with URI
     * @var
     */
    private $regex;
    /**
     * Regex for extracting variables from a URI
     * @var
     */
    private $regex_variables;

    /**
     * Constructs the RouteDefinition and prepares regex
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->methods = [];

        $this->ChangeURI($uri);
    }

    /**
     * Changes URI and all regex with it
     * @param string $uri
     * @return void
     */
    public function ChangeURI(string $uri): void
    {
        $this->uri_definition = $uri;
        $extractedRoute = self::ExtractRegexAndVariableNames($uri);

        $this->regex = $extractedRoute['regex'];
        $this->regex_variables = $extractedRoute['variable_names'];
    }

    /**
     * Defines a method under this route
     * @param string $method HTTP method
     * @param \Closure(Request $req, Response $res): void $callback
     * @param ?\Closure(Request $req, Response $res): void $middleware
     * @throws \ErrorException Thrown if route is already defined
     * @return void
     */
    public function DefineMethod(string $method, \Closure $callback, ?\Closure $middleware): void
    {
        if ($this->IsMethodDefined($method)) {
            throw new ErrorException('Route ' . $method . ' \'' . $this->uri_definition . '\' is already defined');
        }

        $this->methods[$method] = new RouteCallbacks($callback, $middleware);
    }

    /**
     * Checks whether method is defined
     * @param string $method
     * @return bool
     */
    public function IsMethodDefined(string $method): bool
    {
        return isset($this->methods[$method]);
    }

    /**
     * Returns the defined methods for this route
     * @return string[]
     */
    public function GetDefinedMethods(): array
    {
        return array_keys($this->methods);
    }

    /**
     * Check whether URI matches with its definition
     * @param string $uri
     * @return bool|int
     */
    public function MatchesWithURI(string $uri): bool
    {
        return preg_match($this->regex, $uri);
    }

    /**
     * Gets the callback for a given method
     * @param string $method HTTP method
     * @throws \ErrorException Thrown if method is not defined
     * @return RouteCallbacks
     */
    public function GetMethodCallback(string $method): RouteCallbacks
    {
        if (! $this->IsMethodDefined($method)) {
            throw new ErrorException('Route \'' . $this->uri_definition . '\' has no method ' . $method . '');
        }

        return $this->methods[$method];
    }

    /**
     * Returns the variables in a URI
     * @param string $uri
     * @throws \ErrorException When the URI doesn't match the defined regex
     * @return array Array of the variables
     */
    public function GetVariables(string $uri): array
    {
        $routeRegexGroups = [];
        if (! preg_match($this->regex, $uri, $routeRegexGroups)) {
            throw new ErrorException('URI \'' . $uri . '\' does not match with \'' . $this->uri_definition . '\'');
        }

        $variables = [];
        for ($i = 1; $i < count($routeRegexGroups); $i++) {
            $name  = $this->regex_variables[$i - 1];
            $value = $routeRegexGroups[$i];

            $variables[$name] = $value;
        }

        return $variables;
    }

}
