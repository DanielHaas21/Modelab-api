<?php

namespace App\Services\Router;

/**
 * Enum of HTTP request methods
 */
class RequestMethod
{
    /**
     * GET HTTP method
     * @var string
     */
    public const GET = 'GET';
    /**
     * POST HTTP method
     * @var string
     */
    public const POST = 'POST';
    /**
     * PUT HTTP method
     * @var string
     */
    public const PUT = 'PUT';
    /**
     * PATCH HTTP method
     * @var string
     */
    public const PATCH = 'PATCH';
    /**
     * DELETE HTTP method
     * @var string
     */
    public const DELETE = 'DELETE';
}
