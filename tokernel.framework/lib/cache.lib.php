<?php
/**
 * toKernel - Universal PHP Framework.
 * Content caching library.
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    3.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * cache_lib class library.
 *  
 * @author David A. <tokernel@gmail.com>
 */
class cache_lib {

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access protected
	 * @var object
	 */
	protected $lib;

	/**
	 * Cache configuration file.
	 * The real path of this file is: application/config/caching.ini
	 *
	 * @var string
	 * @access protected
	 */
	protected $cache_ini_file = TK_CACHING_CONFIG_INI;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return object
	 */
	public function __construct() {

		$this->lib = lib::instance();

		return $this->instance();
	}

	/**
	 * Return instance of cache library object by configuration section name
	 *
	 * @access public
	 * @param mixed string | NULL $config_section_name
	 * @return object
	 */
	public function instance($config_section_name = NULL) {

		if(is_null($config_section_name)) {
			$config_section_name = 'tokernel_default';
		}

		// Set Configuration file path.
		$conf_ini_file_path = TK_APP_PATH . 'config' . TK_DS . TK_CACHING_CONFIG_INI;

		// Load configuration object
		$config_ini_obj = $this->lib->ini->instance($conf_ini_file_path, $config_section_name, false);

		// Check, configuration values exists and is object.
		if(!is_object($config_ini_obj)) {
			trigger_error('Cannot load configuration `' . $config_section_name . '`.'.
				' File or section not exists. See ' . $conf_ini_file_path, E_USER_ERROR);
		}

		// Append instance name to object
		$config_ini_obj->item_set('instance', $config_section_name, $config_section_name);

		// Define lib name from configuration
		$cache_lib_name = $config_ini_obj->item_get('cache_lib', $config_section_name);

		// Define Base cache lib file path
		$app_base_cache_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'cache' . TK_DS . 'cache_base.lib.php';
		$tk_base_cache_lib_path = TK_PATH . 'lib' . TK_DS . 'cache' . TK_DS . 'cache_base.lib.php';

		// Include base cache lib
		if(file_exists($app_base_cache_lib_path)) {
			require_once ($app_base_cache_lib_path);
		} else {
			require_once ($tk_base_cache_lib_path);
		}

		// Define lib file path.
		$app_cache_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'cache' . TK_DS . $cache_lib_name . '.lib.php';
		$tk_cache_lib_path = TK_PATH . 'lib' . TK_DS . 'cache' . TK_DS . $cache_lib_name . '.lib.php';

		// Include cache lib class
		if(file_exists($app_cache_lib_path)) {
			require_once ($app_cache_lib_path);
		} else {
			require_once ($tk_cache_lib_path);
		}

        // Define cache headers lib file path
        $app_base_cache_headers_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'cache' . TK_DS . 'cache_headers.lib.php';
        $tk_base_cache_headers_lib_path = TK_PATH . 'lib' . TK_DS . 'cache' . TK_DS . 'cache_headers.lib.php';

        // Include Cache headers library
        if(file_exists($app_base_cache_headers_lib_path)) {
            require_once ($app_base_cache_headers_lib_path);
        } else {
            require_once ($tk_base_cache_headers_lib_path);
        }

		// Define configuration array
		$config_arr = $config_ini_obj->section_get($config_section_name);

		// Define cache lib class name
		$class_name = $cache_lib_name . '_lib';
		$cache_obj = new $class_name($config_arr);

		return $cache_obj;

	} // End func instance

} // End class cache_lib

/* End of file */
?>