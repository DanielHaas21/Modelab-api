# Modelab-api
This is an API and backend for [Modelab](https://github.com/DanielHaas21/Modelab)

### Prerequisites

- PHP `>=7.2`

### Installation

Clone the repository

Make sure your DB server is on

#### Windows
Make sure you are in root directory of the project

Run the following command 
```bash
./modelab.cmd build 
```

Or for specfic tasks
- `create-db` for creating the database; adjusting the connection string in the PDO.php and config.php is recommended
- `migrate` for migrating tables
#### Linux
Make sure you are in root directory of the project and have make installed in your system

Run the following command 
```bash
make build 
```

Or for specfic tasks
- `create-db` for creating the database; adjusting the connection string in the PDO.php and config.php is recommended
- `migrate` for migrating tables
## API Structure
App folder structure 
```
├───App
│   ├───bin - .bat and .bash build scripts
│   ├───Database 
│   ├───Controllers
│   ├───Helpers
│   ├───Middleware
│   ├───Models
│   └───Router
├───Config
├───Routes
├───autoload.php - Autoloader of classes 
├───index.php - Entrypoint of the api
├───modelab.cmd - Interface for executing .bat scripts
├───makefile -  Interface for executing .sh scripts
└───.htaccess - Rewriting rules for Apache
``` 

## Naming convetion 
- Regular camel case for normal files
- All capitalized for files executable via .bat and .bash 
- All directories except for routes, config and bin must have a first capital letter
