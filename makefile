# Define paths to your shell scripts
SH_PATH = auto
MIGRATE_SCRIPT = $(SH_PATH)/tables.sh
PDO_SCRIPT = $(SH_PATH)/db.sh
BUILD_SCRIPT  = $(SH_PATH)/build.sh

# Define target names for your migration steps
.PHONY: create-db migrate build

create-db:
	@echo "Running PDO script to initialize database connection..."
	@bash $(PDO_SCRIPT)
	@if [ $$? -ne 0 ]; then \
		echo "Error while initializing PDO."; \
		exit 1; \
	fi
	@echo "PDO initialization completed successfully."

# Migrate tables
migrate:
	@echo "Running migration script to create tables..."
	@bash $(MIGRATE_SCRIPT)
	@if [ $$? -ne 0 ]; then \
		echo "Error while migrating tables."; \
		exit 1; \
	fi
	@echo "Table migration completed successfully."

build:
	@echo "Running build script..."
	@bash $(BUILD_SCRIPT)
	@if [ $$? -ne 0 ]; then \
		echo "Error during build."; \
		exit 1; \
	fi
	@echo "Build process completed successfully."
