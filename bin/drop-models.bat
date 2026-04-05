@echo off
php  bin/actions/DROP_MODELS.php
if %errorlevel% neq 0 (
    echo Error while checking/creating the database.
    exit /b %errorlevel%
)
echo Database script completed successfully.
