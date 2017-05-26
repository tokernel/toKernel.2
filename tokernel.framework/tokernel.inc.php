<?php 
/**
 * toKernel - Universal PHP Framework.
 * Framework loader.
 * 
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   framework
 * @package    toKernel
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    4.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */ 

/**
 * This file included only in application/index.php
 * Example: require('path/to/framework/dir/tokernel.inc.php');
 *
 * Restrict direct access to this file.
 */
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Restricted area.');
}

/* Define start time for debug information */
define('TK_START_RUN', round(microtime(true), 3));

/*
 * To restrict direct access to files excepts index.php, we have to define constant for future check.
 *
 * defined('TK_EXEC') or die('Restricted area.');
 * 
 * This defined constant allows to run files included bellow.
 */
define('TK_EXEC', true);

/*
 * toKernel Framework path.
 * It is possible to save the framework directory in any path.
 * For example:
 *
 * /var/www/html/tokernel.framework/
 * /var/lib/tokernel.framework/
 * /home/{your_username}/tokernel.framework/
 * /home/{your_username}/public_html/tokernel.framework/
 * /usr/local/lib/my-web-framework-core (as you can see, the framework directory name has been renamed).
 */
define('TK_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/*
 * toKernel framework directory name
 * by default: tokernel.framework
 */
define('TK_DIR', basename(dirname(__FILE__)));

/* Include framework constants file */
require(TK_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php');

/*
 * Detect the mode of application runtime - HTTP or CLI.
 * 
 * Note: There are differences between CLI and HTTP application libraries and functionality.
 */
if(!empty($argc) and php_sapi_name() == 'cli') {
	
	/* Define new line character */
	define('TK_NL', "\n");
	
	/* Define Framework's run mode */
	define('TK_RUN_MODE', TK_CLI_MODE);
    
	/*
	 * Execute forever for CLI.
	 * 
	 * Note: The default limit is 30 seconds, defined in php.ini
	 * max_execution_time = 30
	 * 
	 * Note: This function has no effect when PHP is running in safe mode. 
	 * There is no workaround other than turning off safe mode or changing 
	 * the time limit in the php.ini. 
	 */
	set_time_limit(0);
	
	/* Set some configurations for CLI mode */
	ini_set('track_errors', true);
    ini_set('html_errors', false);
    
} else {

	/* Define new line character */
	define('TK_NL', "<br />");
	
	/* Define application run mode */
	define('TK_RUN_MODE', TK_HTTP_MODE);
	
	$argv = NULL;
	
	/* Prepare to compress output content, if the extension "zlib" is loaded */
	if(extension_loaded('zlib') and isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
		   	ob_start("ob_gzhandler");
		   	define('TK_GZIP_OUTPUT', true);
		}
	}
	
	if(!defined('TK_GZIP_OUTPUT')) {
		ob_start();
		define('TK_GZIP_OUTPUT', false);
	}
	
} 

/* Define default timezone before application initialization. */
ini_set('date.timezone', 'America/Los_Angeles');

/* Check Required PHP Version to run Framework. */
if(version_compare(PHP_VERSION, TK_PHP_VERSION_REQUIRED, '<')) {
	die(TK_NL. 'toKernel - Universal PHP Framework v' . TK_VERSION . '.' . 
		TK_NL . 'PHP Version ' . PHP_VERSION . ' is not compatible. ' . 
		TK_NL . ' Version ' . TK_PHP_VERSION_REQUIRED . ' or newer Required.');
}

/* Define Application instance path. */
if(TK_APP_DIR != '') {
	// case 1. /{web_directory}/application/
	define('TK_APP_PATH', TK_ROOT_PATH . TK_APP_DIR . TK_DS);
} else {
	// case 2. /{web_directory}/
	define('TK_APP_PATH', TK_ROOT_PATH);
}

/* Include application constants file */
require_once(TK_APP_PATH . 'config' . TK_DS . 'constants.php');
		
/* Load main library loader class from Framework's kernel. */
require_once(TK_PATH . 'kernel' . TK_DS . 'loaders' . TK_DS . 'lib.class.php');
$lib = lib::instance();

// Include routing library
require_once(TK_PATH . 'kernel' . TK_DS . 'routing' . TK_DS . 'routing.core.class.php');
require_once(TK_PATH . 'kernel'. TK_DS . 'routing' .TK_DS. 'routing.'.TK_RUN_MODE.'.class.php');

/* Include Addons loader library. */
require_once(TK_PATH . 'kernel' . TK_DS . 'loaders' . TK_DS . 'addons.class.php');

/* Include base error/exception handler class. */
require_once(TK_PATH . 'kernel' . TK_DS . 'e-handlers' . TK_DS . 'e.core.class.php');

/* Include extended error handler class depending on application run mode (CLI | HTTP) */
require_once(TK_PATH . 'kernel' . TK_DS . 'e-handlers' . TK_DS . 'e.' . TK_RUN_MODE.'.class.php');

/* Set error and exception handlers. */
set_exception_handler(array('tk_e', 'exception'));
set_error_handler(array('tk_e', 'exception_error_handler'));

/*
 * Set shutdown handler.
 * 
 * When using CLI the shutdown function is not called if 
 * the process gets a SIGINT or SIGTERM. Only the natural 
 * exit of PHP calls the shutdown function.
 */  
register_shutdown_function(array('tk_e', 'shutdown'));

ini_set('log_errors', 0);
ini_set('display_errors', 1);

/*
 * Include Request and Response classes depends on application run mode.
 */
require_once(TK_PATH . 'kernel'.TK_DS.'request'.TK_DS.'request.'.TK_RUN_MODE.'.class.php');
require_once(TK_PATH . 'kernel'.TK_DS.'response'.TK_DS.'response.'.TK_RUN_MODE.'.class.php');

/* Load application configuration object */
$config = $lib->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'application.ini', 'RUN_MODE');

if(!is_object($config)) {
	trigger_error('Application configuration file is not readable or corrupted.', E_USER_ERROR);
	exit(1);
}

/* Configure run mode for error and exception handlers */
tk_e::configure_run_mode($config->section_get('RUN_MODE'));
unset($config);

tk_e::log_debug('', ':==================== START ====================');

tk_e::log_debug('Configured Error Exception/Handler mode', 'Loader');

/* Include application core class. */
require_once(TK_PATH . 'kernel' . TK_DS . 'app' . TK_DS . 'app.core.class.php');

/* Include extended application class depending on application run mode (CLI | HTTP) */
require_once(TK_PATH . 'kernel' . TK_DS . 'app' . TK_DS . 'app.' . TK_RUN_MODE . '.class.php');

/* Include base addon, module, model, view classes */
require_once(TK_PATH . 'base' . TK_DS . 'addon.class.php');
require_once(TK_PATH . 'base' . TK_DS . 'module.class.php');
require_once(TK_PATH . 'base' . TK_DS . 'model.class.php');
require_once(TK_PATH . 'base' . TK_DS . 'view.class.php');

tk_e::log_debug('Loading app base addon, module, model, view classes.', 'Loader');

/* Include application base addon, module, model, view classes */
require_once(TK_APP_PATH . 'base' . TK_DS . 'base_module.class.php');

/* Include all Base files from application/base/* */
$app_base_files = $lib->file->ls(TK_APP_PATH . 'base', '-', false, 'php');

if(!empty($app_base_files)) {
    foreach($app_base_files as $file) {
        require_once(TK_APP_PATH . 'base' . TK_DS . $file);
    }
}

unset($app_base_files);

tk_e::log_debug('Loading app instance', 'Loader');

/* Create application instance. */
$app = app::instance($argv);

/* Include hooks */
require_once(TK_PATH . 'base' . TK_DS . 'hooks_base.class.php');
require_once(TK_APP_PATH . 'hooks' . TK_DS . 'hooks.class.php');

/* Run application. */
if(!$app->run()) { 
	trigger_error('Run Application failed!', E_USER_ERROR);
}
 
/* Define end time/duration for debug information. */
define('TK_END_RUN', round(microtime(true), 3));
define('TK_RUN_DURATION', round((TK_END_RUN - TK_START_RUN), 3));

tk_e::log_debug('Runtime duration: '.TK_RUN_DURATION.' seconds', 'Loader');
tk_e::log_debug('Memory usage: '.$lib->file->format_size(memory_get_peak_usage()), 'Loader');
tk_e::log_debug('', ':===================== END =====================');

/* End of file */