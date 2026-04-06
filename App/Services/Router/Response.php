<?php

namespace App\Services\Router;

/**
 * Response sends data back to the client
 */
class Response
{
    /**
     * Content type for text and HTML
     * @var string
     */
    private static $CONTENT_TYPE_TEXT = 'text/html';
    /**
     * Content type for json
     * @var string
     */
    private static $CONTENT_TYPE_JSON = 'application/json';

    /**
     * Last response
     * @var array{
     *     code: int,
     *     content_type: string,
     *     data: string
     * } | null
     */
    private $response = null;

    /**
     * Sets the response to an Error response
     * @param RequestError $error
     * @return void
     */
    public function SetError(RequestError $error): void
    {
        $json = $this->EncodeJSON($error->GetJSON());

        if ($json === false) {
            $json = '{"message": "Failed to encode JSON", "code": "500"}';
        }

        $this->response = [
            'code'         => 200,
            'content_type' => self::$CONTENT_TYPE_JSON,
            'data'         => $json,
        ];
    }

    /**
     * Sets the response to a JSON response.
     * If fails to encode JSON, sets error response instead
     * @param array $data
     * @param int $code
     * @return void
     */
    public function SetJSON(array $data, int $code = 200): void
    {
        $data['code'] = $code;
        $json = $this->EncodeJSON($data);

        if ($json === false) {
            $this->SetError(new RequestError(500, 'Failed to encode JSON', 'server'));
            return;
        }

        $this->response = [
            'code'         => 200,
            'content_type' => self::$CONTENT_TYPE_JSON,
            'data'         => $json,
        ];
    }

    /**
     * Sets the response to a text response
     * @param string $text
     * @param int $code
     * @return void
     */
    public function SetText(string $text, int $code = 200): void
    {
        $this->response = [
            'code'         => $code,
            'content_type' => self::$CONTENT_TYPE_TEXT,
            'data'         => $text,
        ];
    }

    /**
     * Whether already sent any responses
     * @return bool
     */
    public function HasResponse(): bool
    {
        return $this->response != null;
    }

    /**
     * Send the set response. Ignore if no response is set
     * @return void
     */
    public function Respond(): void
    {
        if (!$this->HasResponse()) {
            return;
        }

        $code = $this->response['code'];
        $contentType = $this->response['content_type'];
        $data = $this->response['data'];

        http_response_code($code);
        header('Content-Type: ' . $contentType . '; charset=utf-8');

        echo $data;
    }

    /**
     * Encodes data into JSON
     * @param array $data
     * @return bool|string false if failed
     */
    private function EncodeJSON(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

}
