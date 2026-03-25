# Modelab-api

This is an API and backend for [Modelab](https://github.com/DanielHaas21/Modelab)

## Contents

- [Modelab-api](#modelab-api)
  - [Contents](#contents)
  - [Installation](#installation)
    - [Prerequisites](#prerequisites)
      - [Windows](#windows)
      - [Linux](#linux)
  - [Docs](#docs)

## Installation

### Prerequisites

- PHP `>=7.2`

1. Clone the repository
2. Make sure your MySQL DB server is on

#### Windows

Make sure you are in root directory of the project

Run the following commands
```bash
./modelab.cmd build
```
Then, assuming you have the following files configured `db.exmaple.php`, `keys.example.php`, `files.example.php`
```bash
./modelab.cmd config
```

Or for specfic tasks
- `create-db` for creating the database; adjusting the connection string in the PDO.php and config.php is recommended
- `drop-db` for dropping the database
- `migrate` for migrating tables

#### Linux

Make sure you are in root directory of the project and have make installed in your system

Run the following commands
```bash
make build
```
Then, assuming you have the following files configured `db.exmaple.php`, `keys.example.php`, `files.example.php`
```bash
make config
```
Or for specfic tasks
- `create-db` for creating the database; adjusting the connection string in the PDO.php and config.php is recommended
- `drop-db` for dropping the database
- `migrate` for migrating tables

## Docs

- [Development](./docs/DEVELOPMENT.md) - Techical overview
- [Endpoints](./docs/ENDPOINTS.md) - All API endpoints described
