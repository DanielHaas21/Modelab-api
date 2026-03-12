<?php

namespace App\Router;

use ErrorException;

/**
 * Request loads client data
 */
class Request
{
    /**
     * Merges data from $_POST and php://input
     * @return array
     */
    public static function GetJSONInput(): array
    {
        $rawData    = json_decode(file_get_contents('php://input'), true);
        $serverPost = isset($_POST) ? $_POST : null;

        $post = [];
        if ($rawData != null) {
            $post = array_merge($post, $rawData);
        }

        if ($serverPost != null) {
            $post = array_merge($post, $serverPost);
        }

        return $post;
    }

    /**
     * Extracts all request headers
     * @return array
     */
    public static function GetAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Extracts the request URI root
     * @return string
     */
    public static function GetURIRoot(): string
    {
        return dirname($_SERVER['PHP_SELF']);
    }

    /**
     * Extracts the request URI
     * @return string
     */
    public static function GetServerRequestURI(): string
    {
        $requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $uriRoot    = self::GetURIRoot();

        $requestUri = substr($requestUri, strlen($uriRoot));

        return RouteDefinition::URI_SEPARATOR . trim($requestUri, RouteDefinition::URI_SEPARATOR);
    }

    /**
     * Request URI
     * @var string
     */
    private $uri;

    /**
     * Route variables
     * @var array
     */
    private $variables;
    /**
     * Loaded JSON array
     * @var array
     */
    private $json;
    /**
     * Request headers
     * @var array
     */
    private $headers;
    /**
     * Data assigned in middleware
     * @var array<string, object>
     */
    private $middlewareData;

    /**
     * Constructs the Request, loads json and headers
     * @param string $uri
     * @param array $variables
     */
    public function __construct(string $uri, array $variables)
    {
        $this->uri       = $uri;
        $this->variables = $variables;
        $this->json      = Request::GetJSONInput();
        $this->headers   = Request::GetAllHeaders();
    }

    /**
     * Gets the request URI
     * @return string
     */
    public function GetURI(): string
    {
        return $this->uri;
    }

    /**
     * Gets the loaded route variables
     * @return array
     */
    public function GetVariables(): array
    {
        return $this->variables;
    }

    /**
     * Gets the loaded JSON array
     * @return array
     */
    public function GetJSON(): array
    {
        return $this->json;
    }

    /**
     * Gets all headers or a specific header
     * @param string|null $key
     * @return string|array|null
     */
    public function GetHeaders(?string $key = null)
    {
        if ($key === null) {
            return $this->headers;
        }

        return $this->headers[$key] ?? null;
    }

    /**
     * Gets the data assigned in middleware
     * @return array
     */
    public function GetMiddlewareData(string $key): ?object
    {
        if (isset($this->middlewareData[$key])) {
            return $this->middlewareData[$key];
        } else {
            return null;
        }
    }

    /**
     * Gets the data assigned in middleware
     * @return array
     */
    public function SetMiddlewareData(string $key, object $value): void
    {
        if (isset($this->middlewareData[$key])) {
            throw new ErrorException('Middleware data \'' . $key . '\' already have a value.');
        }

        $this->middlewareData[$key] = $value;
    }
}
