# File Endpoints

[Back](../ENDPOINTS.md)

These endpoints are used to fetch files.

Defined in: [fileRoutes.php](../../routes/fileRoutes.php)

## Contents

- [File Endpoints](#file-endpoints)
  - [Contents](#contents)
  - [Select Supported File Types](#select-supported-file-types)
    - [Request](#request)
    - [Response](#response)
  - [Select File Metadata](#select-file-metadata)
    - [Request](#request-1)
    - [Response](#response-1)
  - [Select Preview](#select-preview)
    - [Response](#response-2)
  - [Select](#select)
    - [Response](#response-3)

## Select Supported File Types

Returns all supported file types.

| Feature   | Value               |
| --------- | ------------------- |
| URI       | `/file/supported  ` |
| Method    | `POST`              |
| Clearance | Guest               |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "supportedFileTypes": {
        "model": [
            "application/octet-stream",
            // ...
        ],
        "audio": [
            "audio/wav",
            // ...
        ],
        "image": [
            "image/png",
            // ...
        ],
        "other": [
            "application/zip",
            // ...
        ]
    },
}
```

## Select File Metadata

Returns files metadata.

| Feature   | Value               |
| --------- | ------------------- |
| URI       | `/file/{id}/meta  ` |
| Method    | `POST`              |
| Clearance | Guest               |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "meta": {
        "id": 3,
        "name": "house.fbx",
        "fileType": "application/octet-stream"
    }
}
```

## Select Preview

Hosts the preview of a file.

| Feature   | Value                |
| --------- | -------------------- |
| URI       | `/file/{id}/preview` |
| Method    | `GET`                |
| Clearance | Guest                |

### Response

The preivew of a file with the correct headers.

## Select

Hosts the raw file.

| Feature   | Value        |
| --------- | ------------ |
| URI       | `/file/{id}` |
| Method    | `GET`        |
| Clearance | User         |

### Response

The raw file with the correct headers.