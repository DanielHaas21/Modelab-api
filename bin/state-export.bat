@echo off
php bin/actions/STATE_EXPORT.php
if %errorlevel% neq 0 (
    echo Error while exporting state.
    exit /b %errorlevel%
)
echo State export script completed successfully.