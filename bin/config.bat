@echo off
php  auto\CONFIG.php
if %errorlevel% neq 0 (
    echo Error while checking/creating the database.
    exit /b %errorlevel%
)

