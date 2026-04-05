<?php

namespace App\Services\GoogleAuth;

class GoogleUser
{
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $givenName;
    /**
     * @var string
     */
    public $familyName;
    /**
     * @var string
     */
    public $picture;

    public function __construct(array $payload)
    {
        $this->email = $payload['email'];
        $this->givenName = $payload['given_name'];
        $this->familyName = $payload['family_name'];
        $this->picture = $payload['picture'];
    }
}
