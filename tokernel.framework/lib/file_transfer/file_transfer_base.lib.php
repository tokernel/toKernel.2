<?php
/**
 * toKernel - Universal PHP Framework.
 * Base file transfer class library
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
 * @since      File available since Release 2.3.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * db_base_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class file_transfer_base_lib {
	
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
	protected $config;
	
	/**
	 * Connection resource.
	 *
	 * @var resource
	 * @access protected
	 */
	protected $conn_res;
	
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
				
	} // End func __construct
	
	/**
	 * Class destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		
		$this->close();
		
		unset($this->config);
		
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
	 * React to error from catch
	 * In the configuration file, the flags:
	 * log_errors=1
	 * display_errors=1
	 *
	 * dictates this method what to do.
	 *
	 * @access protected
	 * @throws Exception
	 * @param string $message
	 * @param int $code
	 * @return void
	 */
	protected function react_to_error($message, $code = E_USER_ERROR, $file = NULL, $line = NULL) {
		
		if($this->config('log_errors')) {
			tk_e::log($message, $code, $file, $line);
		}
		
		if($this->config('display_errors')) {
			throw new Exception($message);
		}
				
	} // End func react_to_error
	
} /* End of class file_transfer_base_lib */