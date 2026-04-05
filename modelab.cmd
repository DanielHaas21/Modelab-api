@echo off

IF "%1"=="" (
    echo  -------------------------------------------------------
    echo  ^|                 Welcome to Modelab-api              ^| 
    echo  ^|              Before executing any script            ^| 
    echo  ^|  make sure to have your database server turned on!  ^| 
    echo  -------------------------------------------------------
    echo  No action specified. Use: setup, setup-dev, drop-models, state-export, state-import
    exit /b
)

IF "%1"=="setup" (
    echo  -----------------------------------
    echo  ^| Executing script: setup.bat      ^| 
    echo  -----------------------------------
    call bin/setup.bat
    goto end
)

IF "%1"=="setup-dev" (
    echo  -----------------------------------
    echo  ^| Executing script: setup-dev.bat  ^| 
    echo  -----------------------------------
    call bin/setup-dev.bat
    goto end
)

IF "%1"=="drop-models" (
    echo  -----------------------------------
    echo  ^|Executing script: drop-models.bat^| 
    echo  -----------------------------------
    call bin/drop-models.bat
    goto end
)

IF "%1"=="state-export" (
    echo  -----------------------------------
    echo  ^| Executing script: state-export.bat^| 
    echo  -----------------------------------
    call bin/state-export.bat
    goto end
)

IF "%1"=="state-import" (
    echo  -----------------------------------
    echo  ^| Executing script: state-import.bat^| 
    echo  -----------------------------------
    call bin/state-import.bat
    goto end
)

echo  ---------------------------------------------------------------
echo  ^|           Invalid argument. Valid arguments are:            ^| 
echo  ^|  setup, setup-dev, drop-models, state-export, state-import  ^| 
echo  ---------------------------------------------------------------
exit /b

:end
echo  -----------------------------------
echo  ^|         Execution complete      ^| 
echo  -----------------------------------
exit /b