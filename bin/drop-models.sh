#!/bin/bash
source "$(dirname "$0")/utils.sh"

start

start_output
php auto/DROP_MODELS.php
runStatus=$?
end_output
if [ $runStatus -ne 0 ]; then
    fail $runStatus
fi

complete