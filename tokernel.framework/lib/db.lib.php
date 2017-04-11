<?php
/**
 * toKernel - Universal PHP Framework.
 * Database processor class library
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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * db_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_lib {
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access protected
	 * @var object
	 */
	protected $lib;
	
	/**
	 * Database connection configuration file.
	 * The real path of this file is: application/config/databases.ini
	 *
	 * @var string
	 * @access protected
	 */
	protected $conn_ini_file = TK_DB_CONFIG_INI;
	
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->lib = lib::instance();
		
		return $this->instance();
	}
	
	/**
	 * Return instance of database library object by configuration section name
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
		$conf_ini_file_path = TK_APP_PATH . 'config' . TK_DS . TK_DB_CONFIG_INI;
		
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
		$db_lib_name = $config_ini_obj->item_get('driver', $config_section_name);
		
		// Define Base db lib file path
		$app_base_db_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'db' . TK_DS . 'db_base.lib.php';
		$tk_base_db_lib_path = TK_PATH . 'lib' . TK_DS . 'db' . TK_DS . 'db_base.lib.php';
		
		// Include base db lib
		if(file_exists($app_base_db_lib_path)) {
			require_once ($app_base_db_lib_path);
		} else {
			require_once ($tk_base_db_lib_path);
		}
		
		// Define lib file path.
		$app_db_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'db' . TK_DS  . $db_lib_name . TK_DS . 'db_'.$db_lib_name . '.lib.php';
		$tk_db_lib_path = TK_PATH . 'lib' . TK_DS . 'db' . TK_DS . $db_lib_name . TK_DS . 'db_'.$db_lib_name . '.lib.php';
		
		// Include db lib class
		if(file_exists($app_db_lib_path)) {
			require_once ($app_db_lib_path);
		} else {
			require_once ($tk_db_lib_path);
		}
		
		// Define result lib file path.
		$app_db_result_lib_path = TK_APP_PATH . 'lib' . TK_DS . 'db' . TK_DS  . $db_lib_name . TK_DS . 'db_'.$db_lib_name . '_query_result.lib.php';
		$tk_db_result_lib_path = TK_PATH . 'lib' . TK_DS . 'db' . TK_DS  . $db_lib_name . TK_DS . 'db_'.$db_lib_name . '_query_result.lib.php';
		
		// Include db result lib class
		if(file_exists($app_db_result_lib_path)) {
			require_once ($app_db_result_lib_path);
		} else {
			require_once ($tk_db_result_lib_path);
		}
		
		// Define configuration array
		$config_arr = $config_ini_obj->section_get($config_section_name);
		
		// Define class name by driver and return db object
		$db_class = 'db_'.$db_lib_name.'_lib';
		$driver_object = new $db_class($config_arr);
		
		return $driver_object;
		
	} // End func instance
	
} /* End of class db_lib */