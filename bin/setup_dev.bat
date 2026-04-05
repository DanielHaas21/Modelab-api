@echo off
php bin/actions/SETUP_DEV.php
if %errorlevel% neq 0 (
    echo Error while setuping app.
    exit /b %errorlevel%
)
echo App setup script completed successfully.