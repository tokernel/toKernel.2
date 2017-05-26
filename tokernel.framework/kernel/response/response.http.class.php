<?php
/**
 * toKernel - Universal PHP Framework.
 * Main response class for HTTP mode.
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
 * @category   kernel
 * @package    framework
 * @subpackage kernel
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
 * response class
 *
 * @author David A. <tokernel@gmail.com>
 */
class response {
	
	/**
	 * Status of this class instance
	 *
	 * @staticvar object
	 * @access private
	 */
	private static $instance;
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access private
	 */
	private $lib;
	
	/**
	 * Status code
	 *
	 * @var int
	 * @access protected
	 */
	private $status = 200;
	
	/**
	 * @var array
	 * @access private
	 */
	private $headers;
	
	/**
	 * Output content
	 *
	 * @var string
	 * @access private
	 */
	private $output_content;
	
	/**
	 * Configuration file name for status codes
	 *
	 * @var string
	 * @access private
	 */
	private $status_codes_file = 'status_codes.ini';
	
	/**
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
	 */
	final private function __construct() {
		$this->lib = lib::instance();
	}
	
	/**
	 * Prevent cloning of the object.
	 * Trigger E_USER_ERROR if attempting to clone.
	 *
	 * @throws ErrorException
	 * @access public
	 * @return void
	 */
	public function __clone() {
		throw new ErrorException('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
	}
	
	/**
	 * Singleton method used to access the object
	 *
	 * @static
	 * @final
	 * @access public
	 * @return object $instance
	 */
	final public static function instance() {
		
		if(!isset(self::$instance)) {
			$obj = __CLASS__;
			self::$instance = new $obj;
		}
		
		return self::$instance;
		
	} // end func instance
	
	/**
	 * Set status code
	 *
	 * @access public
	 * @param int $code
	 * @return void
	 */
	public function set_status($code) {
		$this->status = $code;
	} // End func set_status
	
	/**
	 * Set headers
	 *
	 * @access public
	 * @param string $header
	 * @return void
	 */
	public function set_headers($header) {
		$this->headers[] = $header;
	} // End func set_headers
	
	/**
	 * Set output content
	 *
	 * @access public
	 * @param mixed $content
	 * @return void
	 */
	public function set_content($content) {
		$this->output_content .= $content;
	} // End func set_content
	
	/**
	 * Final output
	 *
	 * @access public
	 * @return void
	 */
	public function output() {
		
		/* Send headers */
		if(!empty($this->headers)) {
			foreach($this->headers as $header) {
				header($header);
			}
		}
		
		/* Send header status */
		header($this->get_define_status());
		
		/* Output content */
		echo $this->output_content;
		
	} // End func output
	
	/**
	 * Define and return Status code for header
	 *
	 * @access private
	 * @return string
	 */
	private function get_define_status() {
		
		$status_message = '';
		
		// In case if status code is well known, we just defining here instead of load ini file from configuration.
		switch($this->status) {
			case 200:
				$status_message = 'OK';
				break;
			case 201:
				$status_message = 'Created';
				break;
			case 204:
				$status_message = 'No Content';
				break;
			case 301:
				$status_message = 'Moved Permanently';
				break;
			case 400:
				$status_message = 'Bad Request';
				break;
			case 401:
				$status_message = 'Unauthorized';
				break;
			case 403:
				$status_message = 'Forbidden';
				break;
			case 404:
				$status_message = 'Not Found';
				break;
			case 405:
				$status_message = 'Method Not Allowed';
				break;
			case 500:
				$status_message = 'Internal Server Error';
				break;
			case 503:
				$status_message = 'Service Unavailable';
				break;
			default:
				$status_message = $this->get_status_message();
				break;
		}
				
		$php_sapi_name  = substr(php_sapi_name(), 0, 3);
		
		if ($php_sapi_name == 'cgi' || $php_sapi_name == 'fpm') {
			return 'Status: '.$this->status.' '.$status_message;
		} else {
			// Define Server Protocol.
			if(isset($_SERVER['SERVER_PROTOCOL'])) {
				$server_protocol = $_SERVER['SERVER_PROTOCOL'];
			} else {
				$server_protocol = 'HTTP/1.0';
			}
			return $server_protocol.' '.$this->status.' '.$status_message;
		}
		
	} // End func get_define_status
	
	/**
	 * Load initialization file and return status message if exists
	 *
	 * @access private
	 * @throws Exception
	 * @return string
	 */
	private function get_status_message() {
		
		$ini_obj = $this->lib->ini->instance(TK_PATH . 'config' . TK_DS . $this->status_codes_file);
		
		if(!$ini_obj->item_exists($this->status)) {
			throw new ErrorException('Status code `'.$this->status.'` not exists in configuration file!');
		}
		
		return $ini_obj->item_get($this->status);
	}
	
} /* End class response */