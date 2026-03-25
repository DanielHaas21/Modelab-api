# Tag Endpoints

[Back](../ENDPOINTS.md)

These endpoints are used to access and manipulate tags.

Defined in: [tagRoutes.php](../../routes/tagRoutes.php)

## Contents

- [Tag Endpoints](#tag-endpoints)
  - [Contents](#contents)
  - [All](#all)
    - [Request](#request)
    - [Response](#response)
  - [Select](#select)
    - [Request](#request-1)
    - [Response](#response-1)
  - [Create](#create)
    - [Request](#request-2)
    - [Response](#response-2)
  - [Delete](#delete)
    - [Request](#request-3)
    - [Response](#response-3)

## All

Returns all tags.

| Feature   | Value      |
| --------- | ---------- |
| URI       | `/tag/all` |
| Method    | `POST`     |
| Clearance | Guest      |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
  "tags": [
    {
      "id": 2,
      "name": "FBX"
    },
    // ...
  ],
}
```

## Select

Returns data of a tag by it's id.

| Feature   | Value       |
| --------- | ----------- |
| URI       | `/tag/{id}` |
| Method    | `POST`      |
| Clearance | Guest       |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "tag": {
        "id": 2,
        "name": "FBX"
    }
}
```

## Create

Creates a new tag and returns its id. If it exists just returns the id.

| Feature   | Value         |
| --------- | ------------- |
| URI       | `/tag/create` |
| Method    | `POST`        |
| Clearance | Admin         |

### Request

`Content-Type: application/json`
```json
{
    "name": "FBX" // Up to 64 characters
}
```

### Response

```json
{
    "id": 2,
    "message": "Tag already exists" // Or "Tag created"
}
```

## Delete

Deletes the tag.

| Feature   | Value              |
| --------- | ------------------ |
| URI       | `/tag/{id}/delete` |
| Method    | `POST`             |
| Clearance | Admin              |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "id": 2,
    "message": "Tag deleted"
}
```