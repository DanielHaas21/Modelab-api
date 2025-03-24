# Modelab-api
This is an API and backend for [Modelab](https://github.com/DanielHaas21/Modelab)

### Prerequisites

- PHP `>=7.2`

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
└───.htaccess - Rewriting rules for Apache
``` 

## Naming convetion 
- Regular camel case for normal files
- All capitalized for files executable via .bat and .bash 
- All directories except for routes, config and bin must have a first capital letter