<?php
/**
 * toKernel - Universal PHP Framework.
 * Base database class library
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
 * db_base_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_base_lib {
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access protected
	 * @var object
	 */
	protected $lib;
	
	/**
	 * Default configuration
	 *
	 * @access protected
	 * @var array
	 */
	protected $config = array(
		'driver' => 'mysql',
		'host' => 'localhost',
		'port' => '',
		'database' => '',
		'username' => '',
		'password' => '',
		'table_prefix' => '',
		'debug_log' => 0,
		'caching' => 0,
		'charset' => ''
	);
	
	/**
	 * Last Error message
	 *
	 * @access protected
	 * @var null
	 */
	protected $error = NULL;
	
	/**
	 * Table prefix defined in configuration
	 *
	 * @access protected
	 * @var string
	 */
	protected $table_prefix = '';
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config) {
		
		$this->lib = lib::instance();
		
		// Set configuration
		$this->config = array_merge($this->config, $config);
		
		// Set table prefix if defined
		if(isset($this->config['table_prefix'])) {
			$this->table_prefix = $this->config['table_prefix'];
		}
		
	} // End func __construct
	
	/**
	 * Class destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		
		$this->table_prefix = '';
		
		unset($this->config);
		unset($this->error);
		
	} // End func __destruct
	
	/**
	 * Get or Set configuration values
	 *
	 * @access protected
	 * @param string $item
	 * @param mixed $value
	 * @return mixed
	 */
	protected function config($item, $value = NULL) {
		
		if (!is_null($value)) {
			$this->config[$item] = $value;
			return true;
		}
		
		if (isset($this->config[$item])) {
			return $this->config[$item];
		}
		
		return false;
		
	} // End func config
	
	/**
	 * Log error to log file
	 *
	 * @access protected
	 * @param string $message
	 * @param int $code
	 * @return void
	 */
	protected function error_log($message, $code = E_USER_ERROR) {
		
		tk_e::log($message, $code);
		
	} // End func error_log
	
	/**
	 * Log debug info to file
	 *
	 * @access protected
	 * @param string $message
	 * @return void
	 */
	protected function debug_log($message) {
		
		tk_e::log_debug($message, 'Database ('.$this->config['instance'].')');
		
	} // End func error_log
	
	/**
	 * Return last error message
	 *
	 * @access public
	 * @return mixed null | string
	 */
	public function error() {
		return $this->error;
	}
	
	/**
	 * Internal function to check if the table name is empty
	 *
	 * @access protected
	 * @param mixed $table_name
	 * @return bool
	 */
	protected function check_table_name($table_name) {
		
		if(trim($table_name) == '') {
			trigger_error('Table name is empty.', E_USER_ERROR);
		}
		
		return true;
		
	} // End function check_table_name
	
	/**
	 * Internal function to check if the param array is empty
	 *
	 * @access protected
	 * @param mixed $arr
	 * @return bool
	 */
	protected function check_arr($arr) {
		
		if(!is_array($arr) or empty($arr)) {
			trigger_error('Empty data/params Array.', E_USER_ERROR);
		}
		
		return true;
		
	} // End func check_arr
	
} /* End of class db_base_lib */