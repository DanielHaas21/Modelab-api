# Modelab-api

This is an API and backend for [Modelab](https://github.com/DanielHaas21/Modelab)

## Contents

- [Modelab-api](#modelab-api)
  - [Contents](#contents)
  - [Documentation](#documentation)
  - [Setup](#setup)
    - [Prerequisites](#prerequisites)
    - [Setup](#setup-1)
    - [Development](#development)
    - [Other commands](#other-commands)

## Documentation

- [Development](./docs/DEVELOPMENT.md) - Techical overview
- [Design](./docs/DESIGN.md) - About the API design
- [Endpoints](./docs/ENDPOINTS.md) - All API endpoints described

## Setup

### Prerequisites

- PHP `>=7.2`

1. Clone the repository
2. Make sure your MySQL DB server is on

There are many provided actions to help with setup and managment of this app.

On **windows** use:
```bash
./modelab.cmd ACTION
```

On **linux** use:
```bash
make ACTION
```

or execute them manually from `/bin`.

### Setup

**Windows:**

```bash
./modelab.cmd setup
```

**Linux:**
```bash
make setup
```

This will check your `.env` and `/config` configurations, check the DB connection, create the `data/` and `logs/` folder and populate the DB with some base data.

With this, the server is ready for usage.

### Development

**Windows:**

```bash
./modelab.cmd setup-dev
```

**Linux:**
```bash
make setup-dev
```

This will do what setup does and it will load some development assets.

(WIP)

### Other commands

- `drop-models` for dropping the database models
- `state-export` for exporting server state (WIP)
- `state-import` for importing server state (WIP)