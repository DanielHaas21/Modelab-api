@echo off

IF "%1"=="" (
    echo  -------------------------------------------------------
    echo  ^|                 Weclome to Modelab-api              ^| 
    echo  ^|              Before executing any script            ^| 
    echo  ^|  make sure to have your database server turned on!  ^| 
    echo  -------------------------------------------------------
    exit /b
)

IF "%1"=="migrate" (
    echo  -----------------------------------
    echo  ^| Executing script: migrate.bat    ^| 
    echo  -----------------------------------
    
    call bin/migrate.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)

IF "%1"=="build" (
    echo  -----------------------------------
    echo  ^| Executing script: build.bat     ^| 
    echo  -----------------------------------
    
    call bin/build.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)

IF "%1"=="drop-db" (
    echo  -----------------------------------
    echo  ^|Executing script: drop-models.bat^| 
    echo  -----------------------------------
    
    call bin/drop-models.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)

IF "%1"=="config" (
    echo  -----------------------------------
    echo  ^| Executing script:  config.bat   ^| 
    echo  -----------------------------------
    
    call bin/config.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)


echo  ---------------------------------------------------------------
echo  ^|           Invalid argument. Valid arguments are:            ^| 
echo  ^|         create-db, migrate, drop-db, config, build          ^| 
echo  ---------------------------------------------------------------
exit /b
