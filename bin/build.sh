#!/bin/bash
source "$(dirname "$0")/utils.sh"

start
separator
run "config"
separator
run "migrate"
separator
complete