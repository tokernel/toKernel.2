<?php
/**
 * toKernel - Universal PHP Framework.
 * Main request class for CLI (Command line interface) mode.
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
 * request class
 *
 * @author David A. <tokernel@gmail.com>
 */
class request {
	
	/**
	 * Status of this class instance
	 *
	 * @staticvar object
	 * @access private
	 */
	private static $instance;
	
	/**
	 * Status of this class initialization
	 *
	 * @access private
	 * @staticvar bool
	 */
	private static $initialized = false;
			
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;
	
	/**
	 * Response object instance
	 *
	 * @access private
	 * @var object
	 */
	private $response;
	
	/**
	 * CLI Request Configuration
	 *
	 * @access private
	 * @var array
	 */
	private $config;
	
	/**
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
	 */
	final private function __construct() {
		$this->lib = lib::instance();
		$this->response = response::instance();
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
	 * Initialization of request calls at once from application.
	 *
	 * @access public
	 * @throws ErrorException
	 * @param array $args
	 * @param object $config
	 * @return bool
	 */
	public function init($args, $config) {
		
		/* Return true if already initialized */
		if(self::$initialized == true) {
			throw new ErrorException('Request initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!');
		}
				
		// For the first, cleanup all arguments if set in config 1
		/* Clean command line arguments by configuration */
		if($config->item_get('cli_auto_clean_args', 'CLI') == 1) {
			
			tk_e::log_debug('Cleaning command line arguments',
				'app::' . __FUNCTION__);
			
			$args = $this->lib->filter->clean_data($args);
		}
				
		/* Check arguments count */
		if(count($args) < 2) {
			
			tk_e::log_debug('Exit! Invalid Command line arguments.', __CLASS__.'->'.__FUNCTION__);
			tk_e::log("Invalid Command line arguments!", E_USER_NOTICE, __FILE__, __LINE__);
			
			$this->response->output_usage("Invalid Command line arguments!");
			
			exit(1);
		}
		
		/* Show usage on screen and exit, if called help action. */
		if(in_array($args[1], array('--help', '--usage', '-help', '-h', '-?'))) {
			
			tk_e::log_debug('Exit! Show usage/help.', __CLASS__.'->'.__FUNCTION__);
			
			$this->response->output_usage();
			exit(0);
		}
		
		tk_e::log_debug('Parsing arguments', __CLASS__.'->'.__FUNCTION__);
		
		/* Parse Routing and set configuration */
		$this->config = routing::parse_cli($args);
		
		// Clean Addon and Action names if requested with "--" symbols.
		$this->config['addon'] = $this->clean_arg($this->config['addon']);
		$this->config['action'] = $this->clean_arg($this->config['action']);
		
		// Check if action not empty.
		if($this->config['action'] == '') {
			$this->response->output_usage("Action is empty.");
			exit(1);
		}
								
		self::$initialized = true;
		
		return true;
		
	} // End func init
	
	/**
	 * Read data from command line
	 *
	 * @access public
	 * @return string
	 */
	public function in() {
		$handle = trim(fgets(STDIN));
		return $handle;
	} // end func in
	
	/**
	 * Return language prefix
	 *
	 * @access public
	 * @return string
	 */
	public function language_prefix() {
		return $this->config['language_prefix'];
	}
	
	/**
	 * Return addon id
	 *
	 * @access public
	 * @return string
	 */
	public function addon() {
		return $this->config['addon'];
	}
	
	/**
	 * Return action of addon
	 *
	 * @access public
	 * @return string
	 */
	public function action() {
		return $this->config['action'];
	}
		
	/**
	 * Return parameter value by name or parameters array
	 *
	 * @access public
	 * @param string $item
	 * @return mixed array | string | bool
	 */
	public function cli_params($item = NULL) {
		
		/* Return parameters array */
		if(is_null($item)) {
			return $this->config['cli_params'];
		}
		
		/* Return parameter value by name */
		if(isset($this->config['cli_params'][$item])) {
			return $this->config['cli_params'][$item];
		}
		
		/* Parameter not exists */
		return false;
		
	} // end func params
	
	/**
	 * Return parameters count
	 *
	 * @access public
	 * @return integer
	 */
	public function cli_params_count() {
		return count($this->config['cli_params']);
	}
	
	/**
	 * Clean argument
	 * Remove first "--" chars if exists.
	 *
	 * @access protected
	 * @param string $arg
	 * @return string
	 */
	protected function clean_arg($arg) {
		
		if (substr($arg, 0, 2) == '--') {
			$arg = substr($arg, 2);
		}
		
		return $arg;
		
	} // End func clean_arg
		
} /* End class request */