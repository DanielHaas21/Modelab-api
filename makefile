SH_PATH = bin

.PHONY: setup drop-models state-export state-import

default:
	@echo "No action specified. Use: setup|setup-dev|drop-models|state-export|state-import"

setup:
	@bash $(SH_PATH)/setup.sh

setup-dev:
	@bash $(SH_PATH)/setup-dev.sh

drop-models:
	@bash $(SH_PATH)/drop-models.sh

state-export:
	@bash $(SH_PATH)/state-export.sh

state-import:
	@bash $(SH_PATH)/state-import.sh