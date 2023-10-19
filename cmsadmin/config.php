<?php
// HTTP
define('HTTP_SERVER', 'http://www.localhost/9931/cmsadmin/');
define('HTTP_CATALOG', 'http://www.localhost/9931/');

// HTTPS
define('HTTPS_SERVER', 'http://www.localhost/9931/cmsadmin/');
define('HTTPS_CATALOG', 'http://www.localhost/9931/');

// DIR
define('DIR_APPLICATION', realpath(__DIR__ . '/..') . '/cmsadmin/');
define('DIR_SYSTEM', realpath(__DIR__ . '/..') . '/system/');
define('DIR_IMAGE', realpath(__DIR__ . '/..') . '/image/');
define('DIR_STORAGE', realpath(__DIR__ . '/..') . '/system/storage/');
define('DIR_CATALOG', realpath(__DIR__ . '/..') . '/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', '9931');
define('DB_PORT', '3306');
define('DB_PREFIX', '1c0p_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
