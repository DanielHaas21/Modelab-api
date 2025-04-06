SH_PATH = bin

.PHONY: init-db migrate build drop-models config

default:
	@echo "No action specified"

init-db:
	@bash $(SH_PATH)/init-db.sh

migrate:
	@bash $(SH_PATH)/migrate.sh

build:
	@bash $(SH_PATH)/build.sh

drop-models:
	@bash $(SH_PATH)/drop-models.sh
	
config:
	@bash $(SH_PATH)/config.sh