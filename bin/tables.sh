 #!/bin/bash
php auto/PDO.php
if [ $? -ne 0 ]; then
    echo "Error while migrating tables."
    exit 1
fi
echo "Table creation completed successfully."
