SCRIPTS_DIR=$(dirname "$0")
SCRIPT_NAME=$(basename "$0" .sh)

RED="\e[31m"
GREEN="\e[32m"
BLUE="\e[34m"
PURPLE="\e[35m"
WHITE="\e[37m"

BOLD="\e[1m"
RESET="\e[0m"

runStatus=0

function start() {
    echo -e "$SCRIPT_NAME : ${PURPLE}Starting${RESET}"
}

function complete() {
    echo -e "$SCRIPT_NAME : ${GREEN}${BOLD}Completed${RESET}${GREEN} successfully${RESET}"
}

function fail() {
    echo -e "$SCRIPT_NAME : ${RED}${BOLD}Failed${RESET}${RED} with exit code $1${RESET}"
    exit $1
}

function start_output() {
    echo -e "${BLUE}OUTPUT >>>${RESET}${WHITE}"
}

function end_output() {
    echo -e "${BLUE}<<<${RESET}"
}

function separator() {
    printf '%*s\n' "$(tput cols)" '' | tr ' ' '-'
}

function run() {
    scriptName=$1

    "$SCRIPTS_DIR/$scriptName.sh"
    runStatus=$?
    if [ $runStatus -ne 0 ]; then
        separator
        echo -e "$SCRIPT_NAME: ${RED}${BOLD}Failed${RESET}"
        exit $runStatus
    fi
}
