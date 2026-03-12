<?php

namespace App\Router;

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
     * @return array{regex: string, variableNames: array}
     */
    private static function ExtractRegexAndVariableNames(string $uri): array
    {
        $routeParts = array_filter(explode(self::URI_SEPARATOR, $uri));
        $variableNames = [];

        $quotedUriSeparator = preg_quote(self::URI_SEPARATOR, self::URI_SEPARATOR);

        $routeRegex = '';
        foreach ($routeParts as $routePart) {
            $routeRegex .= $quotedUriSeparator;

            $variableMatchGroups = [];
            $variableMatch = preg_match(self::VARIABLE_REGEX, $routePart, $variableMatchGroups);
            if ($variableMatch) {
                $routeRegex .= self::VALUE_REGEX;
                $variableNames[] = $variableMatchGroups[1];
            } else {
                $routeRegex .= preg_quote($routePart, self::URI_SEPARATOR);
            }

        }

        if (strlen($routeRegex) == 0) {
            $routeRegex = $quotedUriSeparator;
        }

        return [
            'regex' => '/^' . $routeRegex . '$/',
            'variableNames' => $variableNames,
        ];
    }

    /**
     * The URI as provided
     * @var string
     */
    private $uriDefinition;
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
    private $regexVariables;

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
        $this->uriDefinition = $uri;
        $extractedRoute = self::ExtractRegexAndVariableNames($uri);

        $this->regex = $extractedRoute['regex'];
        $this->regexVariables = $extractedRoute['variableNames'];
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
            throw new ErrorException('Route ' . $method . ' \'' . $this->uriDefinition . '\' is already defined');
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
            throw new ErrorException('Route \'' . $this->uriDefinition . '\' has no method ' . $method . '');
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
            throw new ErrorException('URI \'' . $uri . '\' does not match with \'' . $this->uriDefinition . '\'');
        }

        $variables = [];
        for ($i = 1; $i < count($routeRegexGroups); $i++) {
            $name  = $this->regexVariables[$i - 1];
            $value = $routeRegexGroups[$i];

            $variables[$name] = $value;
        }

        return $variables;
    }

}
