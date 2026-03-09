@echo off
php auto\MIGRATE.php
if %errorlevel% neq 0 (
    echo Error while migrating tables.
    exit /b %errorlevel%
)
echo Table creation  script completed successfully.