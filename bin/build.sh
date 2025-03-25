#!/bin/bash
source "$(dirname "$0")/utils.sh"

start
separator
run "init-db"
separator
run "migrate"
separator
complete