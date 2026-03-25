# Server Endpoints

[Back](../ENDPOINTS.md)

These endpoints provide general information about the server.

Defined in: [app.php](../../app.php)

## Contents

- [Server Endpoints](#server-endpoints)
  - [Contents](#contents)
  - [Root](#root)
    - [Request](#request)
    - [Response](#response)
  - [Health](#health)
    - [Request](#request-1)
    - [Response](#response-1)

## Root

Returns a static welcome page.

| Feature   | Value |
| --------- | ----- |
| URI       | /     |
| Method    | `GET` |
| Clearance | Guest |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

`Content-Type: text/html`
```html
Modelab API
```

## Health

Returns an overview of the server.

| Feature   | Value     |
| --------- | --------- |
| URI       | `/health` |
| Method    | `POST`    |
| Clearance | Guest     |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "health": {
        "timestamp": 1774446217, // server local timestamp
        "services": {
            "database": true // DB status
        },
        "version": "1.0" // API version
    }
}
```