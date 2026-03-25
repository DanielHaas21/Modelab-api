# Admin Endpoints

[Back](../ENDPOINTS.md)

These endpoints are used to manage the server.

Defined in: [adminRoutes.php](../../routes/adminRoutes.php)

## Contents

- [Admin Endpoints](#admin-endpoints)
  - [Contents](#contents)
  - [Select All Logs](#select-all-logs)
    - [Request](#request)
    - [Response](#response)
  - [Search Logs](#search-logs)
    - [Request](#request-1)
    - [Response](#response-1)

## Select All Logs

Returns all logs in a paginated form. The logs are ordered by their id.

| Feature   | Value            |
| --------- | ---------------- |
| URI       | `/admin/log/all` |
| Method    | `POST`           |
| Clearance | Admin            |

### Request

`Content-Type: application/json`
```json
{
  "page": 0, // The requested page
  "count": 10 // Maximum amount of items per page
}
```

### Response

```json
{
    "logs": [
        {
            "id": 1,
            "status": "error",
            "origin": "server", // this could be the file
            "message": "Failed to init PDO...",
            "date": "2026-03-16T09:37:05+01:00",
        },
        // ...
    ],
    "info": {
        "page": 0, // Returned page
        "count": 5, // Amount of items 
        "pageCount": 2 // The total page count
    },
}
```

## Search Logs

Returns all logs in a paginated form. The logs are ordered by their id.

| Feature   | Value               |
| --------- | ------------------- |
| URI       | `/admin/log/search` |
| Method    | `POST`              |
| Clearance | Admin               |

### Request

`Content-Type: application/json`
```json
{
  "page": 0, // The requested page
  "count": 10, // Maximum amount of items per page
  "statusQuery": ["error", "info"], // valid options are: "error", "info", "warning", "debug"
  "dateStartQuery": "2026-03-16T09:37:05+01:00",
  "dateStartQuery": "2026-08-16T09:37:05+01:00",
}
```

### Response

```json
{
    "logs": [
        {
            "id": 1,
            "status": "error",
            "origin": "server", // this could be the file
            "message": "Failed to init PDO...",
            "date": "2026-03-16T09:37:05+01:00",
        },
        // ...
    ],
    "info": {
        "page": 0, // Returned page
        "count": 5, // Amount of items 
        "pageCount": 2 // The total page count
    },
}
```