# Asset Endpoints

[Back](../ENDPOINTS.md)

These endpoints are used to access and manipulate assets.

Defined in: [assetRoutes.php](../../routes/assetRoutes.php)

## Contents

- [Asset Endpoints](#asset-endpoints)
  - [Contents](#contents)
  - [All](#all)
    - [Request](#request)
    - [Response](#response)
  - [Search](#search)
    - [Request](#request-1)
    - [Response](#response-1)
  - [Select](#select)
    - [Request](#request-2)
    - [Response](#response-2)
  - [Files](#files)
    - [Request](#request-3)
    - [Response](#response-3)
  - [Create](#create)
    - [Request](#request-4)
    - [Response](#response-4)
  - [Update](#update)
    - [Request](#request-5)
    - [Response](#response-5)
  - [Delete](#delete)
    - [Request](#request-6)
    - [Response](#response-6)

## All

Returns all assets in a paginated form. The assets are ordered by their id.

| Feature   | Value        |
| --------- | ------------ |
| URI       | `/asset/all` |
| Method    | `POST`       |
| Clearance | Guest        |

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
    "assets": [
        {
            "id": 1,
            "name": "Medieval House",
            "description": "A simple medieval themed house.",
            "author": "John Doe",
            "category": {
                "name": "3D Models",
                "id": 1
            },
            "tags": [
                {
                "name": "Maya",
                "id": 1
                },
                // ...
            ],
            "created": "2026-03-16T09:37:05+01:00",
            "updated": "2026-03-16T20:59:16+01:00"
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

## Search

Returns all assets in a paginated form with some search parameters. The assets are filtered by the provided parameters and ordered by their id.

| Feature   | Value           |
| --------- | --------------- |
| URI       | `/asset/search` |
| Method    | `POST`          |
| Clearance | Guest           |

### Request

All **query** parameters are optional. However at least one is required.

`Content-Type: application/json`
```json
{
    "page": 0, // The requested page
    "count": 10, // Maximum amount of items per page
    "nameQuery": "Medieval",
    "descriptionQuery": "house",
    "categoryQuery": [2, 4], // The allowed category ids
    "tagQuery": [1, 5], // The allowed tag ids
    "authorQuery": "Doe",
}
```

### Response

```json
{
    "assets": [
        {
            "id": 1,
            "name": "Medieval House",
            "description": "A simple medieval themed house.",
            "author": "John Doe",
            "category": {
            "name": "3D Models",
            "id": 1
            },
            "tags": [
            {
                "name": "Maya",
                "id": 1
            },
            // ...
            ],
            "created": "2026-03-16T09:37:05+01:00",
            "updated": "2026-03-16T20:59:16+01:00"
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

## Select

Returns data of an asset by it's id.

| Feature   | Value         |
| --------- | ------------- |
| URI       | `/asset/{id}` |
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
    "asset": {
        "id": 1,
        "name": "Medieval House",
        "description": "A simple medieval themed house.",
        "author": "John Doe",
        "category": {
            "name": "3D Models",
            "id": 1
        },
        "tags": [
            {
                "name": "Maya",
                "id": 1
            },
            // ...
        ],
        "created": "2026-03-16T09:37:05+01:00",
        "updated": "2026-03-16T20:59:16+01:00"
    }
}
```

## Files

Returns the files info of an asset by it's id.

| Feature   | Value               |
| --------- | ------------------- |
| URI       | `/asset/{id}/files` |
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
    "files": [
        {
            "id": 1,
            "name": "house.fbx",
            "fileType": "application/octet-stream",
            "isHidden": false,
            "isMain": true,
            "isPreview": false
        },
        // ...
    ]
}
```

## Create

Returns all assets in a paginated form with some search parameters. The assets are filtered by the provided parameters and ordered by their id.

| Feature   | Value           |
| --------- | --------------- |
| URI       | `/asset/create` |
| Method    | `POST`          |
| Clearance | Admin           |

### Request

`n` is the index of the current tag. `m` is the index of the current file.

- IsPreview file can be only 1 and it is used in the browser preview.
- IsHidden files are not shown in the model detail carousel.
- IsMain files are first in the model detail carousel.

`Content-Type: multipart/form-data`
| Key                     | Value                           |
| ----------------------- | ------------------------------- |
| name                    | Medieval House                  |
| Description             | A simple medieval themed house. |
| Author                  | John Doe                        |
| categoryId              | 1 (category id)                 |
| tagIds[n]               | 2 (tag id)                      |
| filesMeta[m][isHidden]  | 0 (1 is true, 0 is false)       |
| filesMeta[m][isMain]    | 1 (1 is true, 0 is false)       |
| filesMeta[m][isPreview] | 0 (1 is true, 0 is false)       |
| files[m]                | (raw file data)                 |

### Response

```json
{
  "id": 1,
  "message": "Asset created",
}
```

## Update

Returns all assets in a paginated form with some search parameters. The assets are filtered by the provided parameters and ordered by their id.

| Feature   | Value                |
| --------- | -------------------- |
| URI       | `/asset/{id}/update` |
| Method    | `POST`               |
| Clearance | Admin                |

### Request

`n` is the index of the current tag. `m` is the index of the current file.

- IsPreview file can be only 1 and it is used in the browser preview.
- IsHidden files are not shown in the model detail carousel.
- IsMain files are first in the model detail carousel.
- IsRemoved files are deleted.

The file[m] raw data should be only sent for new files. 
Otherwise send the filesMeta[m][id].

`Content-Type: multipart/form-data`
| Key                     | Value                           |
| ----------------------- | ------------------------------- |
| name                    | Medieval House                  |
| Description             | A simple medieval themed house. |
| Author                  | John Doe                        |
| categoryId              | 1 (category id)                 |
| tagIds[n]               | 2 (tag id)                      |
| filesMeta[m][isHidden]  | 0 (1 is true, 0 is false)       |
| filesMeta[m][isMain]    | 1 (1 is true, 0 is false)       |
| filesMeta[m][isPreview] | 0 (1 is true, 0 is false)       |
| filesMeta[m][isRemoved] | 0 (1 is true, 0 is false)       |
| filesMeta[m][id]        | 0 (file meta id)                |
| files[m]                | (raw file data)                 |

### Response

```json
{
  "id": 1,
  "message": "Asset updated",
}
```

## Delete

Deletes the asset with all its files and returns the deleted asset id.

| Feature   | Value                |
| --------- | -------------------- |
| URI       | `/asset/{id}/delete` |
| Method    | `POST`               |
| Clearance | Guest                |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "id": 1,
    "message": "Asset deleted"
}
```