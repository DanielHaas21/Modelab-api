# User Endpoints

[Back](../ENDPOINTS.md)

These endpoints provide information about the user and loging in.

Defined in: [userRoutes.php](../../routes/userRoutes.php)

## Login

Uses Google OAuth to authenticate and creates a session token for the user.

| Feature   | Value         |
| --------- | ------------- |
| URI       | `/user/login` |
| Method    | `POST`        |
| Clearance | Guest         |

### Request

`Content-Type: application/json`
```json
{
    "accessToken": "google_oauth_token"
}
```

### Response

```json
{
    "token": "session_token" // the bearer session token
}
```

## Info

Returns information about the user.

| Feature   | Value         |
| --------- | ------------- |
| URI       | `/user/login` |
| Method    | `POST`        |
| Clearance | Guest         |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "user": {
        "email": "john.doe@example.com",
        "givenName": "John",
        "familyName": "Doe",
        "picture": "https://example.com/image.png",
        "clearance": 2
    }
}
```