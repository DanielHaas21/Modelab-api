@echo off
php  App\database\PDO.php
if %errorlevel% neq 0 (
    echo Error while checking/creating the database.
    exit /b %errorlevel%
)
echo Database check completed successfully.
