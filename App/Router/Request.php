<?php
namespace App\Router;

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
        $raw_data    = json_decode(file_get_contents('php://input'), true);
        $server_post = isset($_POST) ? $_POST : null;

        $post = [];
        if ($raw_data != null) {
            $post = array_merge($post, $raw_data);
        }

        if ($server_post != null) {
            $post = array_merge($post, $server_post);
        }

        return $post;
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
        $request_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $uri_root    = self::GetURIRoot();

        $request_uri = substr($request_uri, strlen($uri_root));

        return RouteDefinition::URI_SEPARATOR . trim($request_uri, RouteDefinition::URI_SEPARATOR);
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
     * Constructs the Request, loads json
     * @param string $uri
     * @param array $variables
     */
    public function __construct(string $uri, array $variables)
    {
        $this->uri       = $uri;
        $this->variables = $variables;
        $this->json      = Request::GetJSONInput();
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

}
