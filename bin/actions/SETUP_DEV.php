<?php

/**
 * This is a bash-bat executable script
 * !DO NOT INCLUDE IT ANYWHERE!
 */

/**
 * This action setups the entire backend for development.
 */

require_once __DIR__ . '/../../autoload.php';

// .env
require __DIR__ . '/scripts/validate_env.php';

// Database
require __DIR__ . '/scripts/validate_db.php';

// Logging
require __DIR__ . '/scripts/validate_logging.php';

// File saving
require __DIR__ . '/scripts/validate_asset_files.php';

// Populate DB
require __DIR__ . '/scripts/populate_db.php';

// Load development assets
require __DIR__ . '/scripts/load_dev_assets.php';
