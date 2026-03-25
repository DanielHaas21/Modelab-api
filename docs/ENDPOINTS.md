# Endpoints

[Back](../README.md)

## Base

This API accepts `POST` as its primary method along with JSON as the primary data format.

For `POST` methods the result is always:
```json
{
    "data": {}, // Some data, specified by the endpoint
    "code": 200 // HTTP Response code
}
```

## Authentication

All authorized endpoints use 'Bearer Authentication'.
The bearer token is the user's specified session token.

This session token expires in 2 days (configured in [LoginSession.MAX_TOKEN_REFRESH_INTERVAL_SECONDS](../App/Models/Auth/LoginSession.php)).
To make it not expire, it is refreshed each API call.

## Endpoints

All endpoints are sorted by their clearance.

- [Server](./endpoints/SERVER.md) - General server endpoints, like health
- [User](./endpoints/USER.md) - Authentication and user data
- [Tag](./endpoints/TAG.md) - Tag fetching and management
- [Category](./endpoints/CATEGORY.md) - Category fetching and management
- [Asset](./endpoints/ASSET.md) - Asset fetching and management
- [File](./endpoints/FILE.md) - File fetching
- [Admin](./endpoints/ADMIN.md) - Admin panel endpoints