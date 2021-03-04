<?php
/**copyright**/

/* define system variables */

define('RL_DS', DIRECTORY_SEPARATOR);

//debug manager, set true to enable, false to disable
define('RL_DEBUG', false);
define('RL_DB_DEBUG', false);
define('RL_MEMORY_DEBUG', false);
define('RL_AJAX_DEBUG', false);

// mysql credentials
define('RL_DBPORT', '3306');
define('RL_DBHOST', 'sql594.your-server.de');
define('RL_DBUSER', 'svejetr_1');
define('RL_DBPASS', 'fvS2ze3DcVWg9F1A');
define('RL_DBNAME', 'svejetr_db1');
define('RL_DBPREFIX', 'fl_');

// system paths
define('RL_DIR', '');
define('RL_ROOT', '/usr/www/users/svejetr' . RL_DS . RL_DIR);
define('RL_INC', RL_ROOT . 'includes' . RL_DS);
define('RL_CLASSES', RL_INC . 'classes' . RL_DS);
define('RL_CONTROL', RL_INC . 'controllers' . RL_DS);
define('RL_LIBS', RL_ROOT . 'libs' . RL_DS);
define('RL_TMP', RL_ROOT . 'tmp' . RL_DS);
define('RL_UPLOAD', RL_TMP . 'upload' . RL_DS);
define('RL_FILES', RL_ROOT . 'files' . RL_DS);
define('RL_PLUGINS', RL_ROOT . 'plugins' . RL_DS);
define('RL_CACHE', RL_TMP . 'cache_337424711' . RL_DS);

// system URLs
define('RL_URL_HOME', 'https://www.svejetu.me/');
define('RL_FILES_URL', RL_URL_HOME . 'files/');
define('RL_LIBS_URL', RL_URL_HOME . 'libs/');
define('RL_PLUGINS_URL', RL_URL_HOME . 'plugins/');

//system admin paths
define('ADMIN', 'back');
define('ADMIN_DIR', ADMIN . RL_DS);
define('RL_ADMIN', RL_ROOT . ADMIN . RL_DS);
define('RL_ADMIN_CONTROL', RL_ADMIN . 'controllers' . RL_DS);

//memcache server host and port
define('RL_MEMCACHE_HOST', '127.0.0.1');
define('RL_MEMCACHE_PORT', 11211);

/* YOU ARE NOT PERMITTED TO MODIFY THE CODE BELOW */
define('RL_SETUP', 'JGxpY2Vuc2VfZG9tYWluID0gInN2ZWpldHUubWUiOyRsaWNlbnNlX251bWJlciA9ICJGTDdZTlI2NkU5RlUiOw==');
/* END CODE */

/* define system variables end */
