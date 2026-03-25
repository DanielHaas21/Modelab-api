# Server Endpoints

[Back](../ENDPOINTS.md)

These endpoints provide general information about the server.

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

The root endpooint returns a static page.

| Feature | Value |
| ------- | ----- |
| URI     | /     |
| Method  | `GET` |
| Auth    | None  |

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

The health endpooint returns an overview of the server.

| Feature | Value     |
| ------- | --------- |
| URI     | `/health` |
| Method  | `POST`    |
| Auth    | None      |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
  "timestamp": 1774446217, // server local timestamp
  "services": {
    "database": true // DB status
  },
  "version": "1.0", // API version
}
```