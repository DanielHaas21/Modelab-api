#!/bin/bash
php App/database/PDO.php
if [ $? -ne 0 ]; then
    echo "Error while checking/creating the database."
    exit 1
fi
echo "Database check completed successfully."
