<?php

namespace App\Middleware\GoogleAuth;

require_once __DIR__ . '/google-api-php-client--PHP7.0/vendor/autoload.php';
require_once __DIR__ . '/../../../config/keys.php';

use Google_Client;

class GoogleAuth
{
    /**
     * Gets user from the Google api client
     * @param mixed $idToken Credential of the user
     * @return array{error: string, user: ?GoogleUser, success: bool}
     */
    public function Login(?string $idToken): array
    {
        if ($idToken === null) {
            return [
                'success' => false,
                'error' => 'No ID token',
                'user' => null
            ];
        }

        $client = new Google_Client(['client_id' => KEYS_CONFIG['google']['clientId']]);
        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            return [
                'success' => false,
                'error' => 'Invalid ID token',
                'user' => null
            ];
        }

        return [
            'success' => true,
            'user' => new GoogleUser($payload),
            'error' => ''
        ];
    }
}
