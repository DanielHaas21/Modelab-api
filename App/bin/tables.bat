@echo off
php App\database\MIGRATE.php
if %errorlevel% neq 0 (
    echo Error while migrating tables.
    exit /b %errorlevel%
)
echo Table creation completed successfully.