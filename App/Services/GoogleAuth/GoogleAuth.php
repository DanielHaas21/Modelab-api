<?php

namespace App\Services\GoogleAuth;

require_once __DIR__ . '/google-api-php-client--PHP7.0/vendor/autoload.php';

use Google_Client;
use Google_Service_Oauth2;

class GoogleAuth
{
    /**
     * Gets user from the Google api client using an Access Token
     * @param string|null $accessToken The token received from useGoogleLogin
     * @return array{error: string, user: ?GoogleUser, success: bool}
     */
    public static function Login(?string $accessToken): array
    {
        if ($accessToken === null) {
            return [
                'success' => false,
                'error' => 'No Access token',
                'user' => null
            ];
        }

        $client = new Google_Client();
        $client->setAccessToken($accessToken);

        $oauth2 = new Google_Service_Oauth2($client);

        try {
            $userinfo = $oauth2->userinfo->get();

            $payload = [
                'sub' => $userinfo->id,
                'email' => $userinfo->email,
                'name' => $userinfo->name,
                'picture' => $userinfo->picture,
                'given_name' => $userinfo->givenName,
                'family_name' => $userinfo->familyName,
                'email_verified' => $userinfo->verifiedEmail
            ];

            return [
                'success' => true,
                'user' => new GoogleUser($payload),
                'error' => ''
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Invalid or expired Access token: ' . $e->getMessage(),
                'user' => null
            ];
        }
    }
}
