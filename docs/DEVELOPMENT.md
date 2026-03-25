# Development

[Back](../README.md)

## Contents

- [Development](#development)
  - [Contents](#contents)
  - [Naming convetion](#naming-convetion)
    - [Files and Directories](#files-and-directories)
    - [Code](#code)
  - [Folder structure](#folder-structure)
  - [Set up development enviroment](#set-up-development-enviroment)
      - [VS Code](#vs-code)

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

## Folder structure

```
├───.vscode - settings for VS Code
│   ├───php-cs-fixer.phar - PHP code formatter
│   └───settings.json - VS Code settings
├───.github - settings for Github
│   ├───workflows
│       └───deploy.yml - Github deploy script
├───App - Definitions of classes
│   ├───Database
│   ├───Controllers
│   ├───Helpers
│   ├───Middleware
│   ├───Models
│   ├───Router
│   └───Validators
├───config
│   ├───someconfig.php - actual config file, can be auto generated
│   └───someconfig.example.php - config file template
├───auto - Scripts call only from build scripts
├───bin - .bat and .bash build scripts
├───routes - Defined routes, include only from index.php
├───autoload.php - Autoloader of classes
├───index.php - Entrypoint and setup of the server
├───app.php - The server
├───modelab.cmd - Interface for executing .bat scripts
├───makefile -  Interface for executing .sh scripts
├───.gitignore - Rules for Git
└───.htaccess - Rewriting rules for Apache
```

## Set up development enviroment

#### VS Code

1. Install [PHP](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode) extension 
2. Install [php cs fixer](https://marketplace.visualstudio.com/items?itemName=junstyle.php-cs-fixer) extension
3. Make sure .vscode/settings.json are loaded and used (if using workspaces, copy settings to your VS Code settings)