<?php

require_once __DIR__.'/../vendor/autoload.php';

define('BASE_PATH', __DIR__ . '/..');
define('ROOT_PATH', BASE_PATH);
define('APP_PATH', ROOT_PATH . '/app');
define('COMMAND_PATH', APP_PATH . '/commands');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('APP_START', microtime(true));

require_once ROOT_PATH . '/bootstrap/console/Bootstrap.php';

(new Bootstrap())->init();
