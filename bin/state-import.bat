@echo off
php bin/actions/STATE_IMPORT.php
if %errorlevel% neq 0 (
    echo Error while importing state.
    exit /b %errorlevel%
)
echo State import script completed successfully.