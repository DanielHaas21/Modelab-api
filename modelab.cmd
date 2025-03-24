@echo off

IF "%1"=="" (
    echo  -------------------------------------------------------
    echo  ^|                 Weclome to Modelab-api              ^| 
    echo  ^|              Before executing any script            ^| 
    echo  ^|  make sure to have your database server turned on!  ^| 
    echo  -------------------------------------------------------
    exit /b
)

IF "%1"=="create-db" (
    echo  -----------------------------------
    echo  ^| Executing script: db.bat        ^| 
    echo  -----------------------------------

    call App/bin/db.bat
    
    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)

IF "%1"=="migrate" (
    echo  -----------------------------------
    echo  ^| Executing script: tables.bat    ^| 
    echo  -----------------------------------
    
    call App/bin/tables.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)

IF "%1"=="build" (
    echo  -----------------------------------
    echo  ^| Executing script: build.bat     ^| 
    echo  -----------------------------------
    
    call App/bin/build.bat

    echo  -----------------------------------
    echo  ^|         Execution complete      ^| 
    echo  -----------------------------------

    exit /b
)


echo  ---------------------------------------------------------------
echo  ^|  Invalid argument. Valid arguments are: create-db, migrate  ^| 
echo  ---------------------------------------------------------------
exit /b
