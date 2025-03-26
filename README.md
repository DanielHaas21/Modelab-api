# Modelab-api
This is an API and backend for [Modelab](https://github.com/DanielHaas21/Modelab)

### Prerequisites

- PHP `>=7.2`

### Installation

1. Clone the repository
2. Make sure your DB server is on

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

### Set up development enviroment
#### VS Code
1. Install [PHP](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode) extension 
2. Install [php cs fixer](https://marketplace.visualstudio.com/items?itemName=junstyle.php-cs-fixer) extension
3. Make sure .vscode/settings.json are loaded and used (if using workspaces, copy settings to your VS Code settings)


## API Structure
App folder structure
```
├───.vscode - settings for VS Code
│   ├───php-cs-fixer.phar - PHP code formatter
│   └───settings.json - VS Code settings
├───App - Definitions of classes
│   ├───Database
│   ├───Controllers
│   ├───Helpers
│   ├───Middleware
│   ├───Models
│   └───Router
├───config
│   ├───someconfig.php - actual config file
│   └───someconfig.example.php - config file template
├───auto - Scripts call only from build scripts
├───bin - .bat and .bash build scripts
├───routes - Defined routes, include only from index.php
├───autoload.php - Autoloader of classes
├───index.php - Entrypoint of the api
├───modelab.cmd - Interface for executing .bat scripts
├───makefile -  Interface for executing .sh scripts
├───.gitignore - Rules for Git
└───.htaccess - Rewriting rules for Apache
```


## Naming convetion
### Files and Directories
- Executable files via .bat and .bash -> UPPER_SNAKE_CASE
- Namespace directories (eg. all directories under \App) -> UpperCamelCase
- class files -> same as class name
- other files and directories are camelCase
### Code
- classes -> UpperCamelCase
- methods/functions -> UpperCamelCase
- variables -> camelCase
- constants -> UPPER_SNAKE_CASE
