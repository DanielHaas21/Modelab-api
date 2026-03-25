# Category Endpoints

[Back](../ENDPOINTS.md)

These endpoints are used to access and manipulate categories.

Defined in: [categoryRoutes.php](../../routes/categoryRoutes.php)

## Contents

- [Category Endpoints](#category-endpoints)
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

Returns all categories.

| Feature   | Value           |
| --------- | --------------- |
| URI       | `/category/all` |
| Method    | `POST`          |
| Clearance | Guest           |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "categories": [
        {
            "id": 2,
            "name": "3D Models"
        },
        // ...
    ],
}
```

## Select

Returns data of a category by it's id.

| Feature   | Value            |
| --------- | ---------------- |
| URI       | `/category/{id}` |
| Method    | `POST`           |
| Clearance | Guest            |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "category": {
        "id": 2,
        "name": "3D Models"
    }
}
```

## Create

Creates a new category and returns its id. If it exists just returns the id.

| Feature   | Value              |
| --------- | ------------------ |
| URI       | `/category/create` |
| Method    | `POST`             |
| Clearance | Admin              |

### Request

`Content-Type: application/json`
```json
{
    "name": "3D Models" // Up to 64 characters
}
```

### Response

```json
{
    "id": 2,
    "message": "Category already exists" // Or "Category created"
}
```

## Delete

Deletes the category.

| Feature   | Value                   |
| --------- | ----------------------- |
| URI       | `/category/{id}/delete` |
| Method    | `POST`                  |
| Clearance | Admin                   |

### Request

`Content-Type: application/json`
```json
{}
```

### Response

```json
{
    "id": 2,
    "message": "Category deleted"
}
```