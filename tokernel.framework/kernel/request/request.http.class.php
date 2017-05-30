<?php
/**
 * toKernel - Universal PHP Framework.
 * Main request class for HTTP mode.
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
	 * @staticvar boolean
	 */
	private static $initialized = false;
		
	/**
	 * Interface configuration array
	 *
	 * @access private
	 * @var array
	 */
	private $config;
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access protected
	 * @var object
	 */
	protected $lib;
		
	// array
	protected $input;
		
	/**
	 * Deprecated globals, will removed.
	 *
	 * @access private
	 * @var array
	 */
	private $globals_to_remove = array(
		'HTTP_ENV_VARS',
		'HTTP_POST_VARS',
		'HTTP_GET_VARS',
		'HTTP_COOKIE_VARS',
		'HTTP_SERVER_VARS',
		'HTTP_POST_FILES',
	);
	
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
	 * Initialization of request calls at once from application.
	 *
	 * @access public
	 * @throws ErrorException
	 * @param object $config
	 * @return bool
	 */
	public function init($config) {
		
		/* Return true if already initialized */
		if(self::$initialized == true) {
			throw new ErrorException('Request initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!');
		}
		
		/* Clean globals and XSS by configuration */
		if($config->item_get('auto_clean_globals', 'HTTP') == 1) {
			
			tk_e::log_debug('Cleaning $GLOBALS', 'app::' . __FUNCTION__);
			
			/* Prevent malicious GLOBALS overload attack */
			if(isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
				trigger_error('Global variable overload attack detected! Request aborted.', E_USER_ERROR);
				exit(1);
			}
			
			/*
			* Remove globals which exists in removable_globals,
			* otherwise do clean by received arguments.
			*/
			foreach($GLOBALS as $g_key => $g_value) {
				if(in_array($g_key, $this->globals_to_remove)) {
					unset($GLOBALS[$g_key]);
				}
			} // end foreach
			
			/* Define methods other than GET and POST */
			$method = $this->method();
			
			if($method != 'GET' and $method != 'POST') {
				
				$_GLOBAL_REQUEST_ = array();
				parse_str(file_get_contents('php://input'), $_GLOBAL_REQUEST_);
				$GLOBALS['_' . $method] = $_GLOBAL_REQUEST_;
				
				// Add these request vars into _REQUEST
				$_REQUEST = $_GLOBAL_REQUEST_ + $_REQUEST;
				
			}
			
			/*
			 * Unset $_GET.
			 */
			$GLOBALS['_GET'] = array();
			
			/* GLOBALS to clean */
			$globals_to_clean = array(
				'_POST',
				'_REQUEST',
				'_COOKIE',
				'_FILES',
				'_SERVER',
				'_SESSION',
				'argv'
			);
			
			/* Clean globals */
			foreach($globals_to_clean as $global_name) {
				
				if(isset($GLOBALS[$global_name])) {
					
					$GLOBALS[$global_name] = $this->lib->filter->clean_data($GLOBALS[$global_name]);
					
					/* Clean for xss */
					if($config->item_get('auto_clean_globals_xss', 'HTTP') == 1) {
						$GLOBALS[$global_name] = $this->lib->filter->clean_xss($GLOBALS[$global_name], false);
					}
					
				} else {
					$GLOBALS[$global_name] = array();
				}
				
			}
			
		}
				
		/* Clean url by configuration, before initialize */
		if($config->item_get('auto_clean_url', 'HTTP') == 1) {
			
			tk_e::log_debug('Cleaning URL', 'app::' . __FUNCTION__);
						
			if(isset($_SERVER['HTTP_HOST'])) {
				
				/* Ensure hostname only contains characters allowed in hostnames */
				$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
				
				if(!$this->lib->valid->http_host($_SERVER['HTTP_HOST'])) {
					trigger_error('Invalid HTTP_HOST `'.$_SERVER['HTTP_HOST'].'`!', E_USER_ERROR);
				}
				
			} else {
				$_SERVER['HTTP_HOST'] = '';
			}
			
			/* Clean some globals before using */
			
			$_SERVER['REQUEST_URI']  = $this->lib->filter->clean_data($_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI']  = $this->lib->filter->clean_xss($_SERVER['REQUEST_URI'], true);
			$_SERVER['QUERY_STRING'] = $this->lib->filter->clean_data($_SERVER['QUERY_STRING']);
			$_SERVER['QUERY_STRING'] = $this->lib->filter->clean_xss($_SERVER['QUERY_STRING'], true);
			
			if(isset($_SERVER['REDIRECT_URL'])) {
				$_SERVER['REDIRECT_URL'] = $this->lib->filter->clean_data($_SERVER['REDIRECT_URL']);
				$_SERVER['REDIRECT_URL'] = $this->lib->filter->clean_xss($_SERVER['REDIRECT_URL'], true);
			}
			
			if(isset($_SERVER['REDIRECT_QUERY_STRING'])) {
				$_SERVER['REDIRECT_QUERY_STRING'] = $this->lib->filter->clean_data($_SERVER['REDIRECT_QUERY_STRING']);
				$_SERVER['REDIRECT_QUERY_STRING'] = $this->lib->filter->clean_xss($_SERVER['REDIRECT_QUERY_STRING'], true);
			}
			
			if(isset($_SERVER['argv'][0])) {
				$_SERVER['argv'][0] = $this->lib->filter->clean_data($_SERVER['argv'][0]);
				$_SERVER['argv'][0] = $this->lib->filter->clean_xss($_SERVER['argv'][0], true);
			}
			
			/* Get query string source for parsing */
			$_SERVER['QUERY_STRING'] = trim($_SERVER['QUERY_STRING']);
		}
		
		// Initialize interface configuration
		$request_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$result = routing::parse_http_interface($request_url);
		$this->config = $result['request'];
				
		self::$initialized = true;
				
		return $result['interface'];
				
	} // End func init
		
	/**
	 * Return subdomain if exists in host name.
	 *
	 * @access public
	 * @return mixed string | bool
	 */
	public function subdomain($item = NULL) {
		
		if(is_null($item)) {
			return $this->config['subdomains'];
		}
		
		if(isset($this->config['subdomains'][$item])) {
			return $this->config['subdomains'][$item];
		}
		
		return false;
		
	} // End func subdomain
	
	/**
	 * Return detected language prefix
	 *
	 * @access public
	 * @return string
	 */
	public function language_prefix() {
		return $this->config['language_prefix'];
	}
			
	/**
	 * Return base url
	 *
	 * @access public
	 * @return string
	 * @todo add language prefix if parse allowed.
	 */
	public function base_url() {
		
		$base_url = '';
		
		if($this->is_https()) {
			$base_url .= 'https://';
		} else {
			$base_url .= 'http://';
		}
		
		$base_url .= $this->config['hostname'] . '/';
				
		if($this->config['interface_path'] != '') {
			$base_url .= $this->config['interface_path'] . '/';
		}
		
		return $base_url;
	}
	
	public function url() {
		return $this->config['url'];
	}
	
	/**
	 * Return detected addon
	 *
	 * @access public
	 * @return string
	 */
	public function addon() {
		return $this->config['addon'];
	}
	
	/**
	 * Return detected action
	 *
	 * @access public
	 * @return string
	 */
	public function action() {
		return $this->config['action'];
	}
		
	/**
	 * Return true if the request protocol is https
	 *
	 * @access public
	 * @return bool
	 * @since version 2.5.0
	 */
	public function is_https() {
		
		if($this->server('HTTPS') and $this->server('HTTPS') != 'off') {
			return true;
		} else {
			return false;
		}
		
	} // End func is_https
		
	/**
	 * Return exploded parts from url
	 *
	 * @access public
	 * @param int $index
	 * @return mixed
	 * @since version 2.3.0
	 */
	public function url_parts($index = NULL) {
		
		if(is_null($index)) {
			return $this->config['url_parts'];
		}
		
		if(isset($this->config['url_parts'][$index])) {
			return $this->config['url_parts'][$index];
		}
		
		return false;
		
	} // End func url_parts
	
	/**
	 * Return count of url parts
	 *
	 * @access public
	 * @return integer
	 * @since version 2.3.0
	 */
	public function url_parts_count() {
		return count($this->config['url_parts']);
	}
	
	/**
	 * Return URL parameter value by index starting from 0 or all URL parameters array
	 *
	 * @access public
	 * @param string $index
	 * @return mixed
	 */
	public function url_params($index = NULL) {
		
		if(is_null($index)) {
			return $this->config['url_params'];
		}
		
		if(isset($this->config['url_params'][$index])) {
			return $this->config['url_params'][$index];
		}
		
		return false;
		
	} // end func url_params
	
	/**
	 * Return count of parameters
	 *
	 * @access public
	 * @return integer
	 */
	public function url_params_count() {
		return count($this->config['url_params']);
	}
	
	public function interface_name() {
		return $this->config['interface_name'];
	}
	
	public function method() {
		if(isset($_SERVER['REQUEST_METHOD'])) {
			return $_SERVER['REQUEST_METHOD'];
		}
	}
	
	public function is_ajax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			$_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
	}
		
	// return POST/PUT/DELETE and other request data depends on request method.
	public function input($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('_REQUEST', $item, $encode_html_entities, $clean_xss, $strip_tags);
	}
	
	/**
	 * Get, clean global vars
	 *
	 * @access protected
	 * @param string $global_index
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	protected function get_globals($global_index, $item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		
		if(!isset($GLOBALS[$global_index])) {
			trigger_error($global_index . ' not defined.', E_USER_WARNING);
		}
		
		// Item not exists
		if(!is_null($item) and !isset($GLOBALS[$global_index][$item])) {
			return false;
		}
		
		if(is_null($item)) {
			// Return array
			$data = $GLOBALS[$global_index];
		} else {
			// Return item from array
			$data = $GLOBALS[$global_index][$item];
		}
		
		if($clean_xss == true) {
			$data = $this->clean_xss($data, $strip_tags);
		} elseif ($strip_tags == true) {
			$data = strip_tags($data);
		}
		
		if($encode_html_entities == true) {
			$data = $this->lib->filter->encode_html_entities($data);
		}
		
		return $data;
		
	} // End func get_globals
		
	/**
	 * Return cleaned _COOKIE array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	public function cookie($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('_COOKIE', $item, $encode_html_entities, $clean_xss, $strip_tags);
	} // end func cookie
	
	/**
	 * Return cleaned _FILES array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	public function files($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('_FILES', $item, $encode_html_entities, $clean_xss, $strip_tags);
	} // end func files
	
	/**
	 * Return cleaned _SERVER array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	public function server($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('_SERVER', $item, $encode_html_entities, $clean_xss, $strip_tags);
	} // end func server
	
	/**
	 * Return cleaned _SESSION array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	public function session($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('_SESSION', $item, $encode_html_entities, $clean_xss, $strip_tags);
	} // end func server
	
	/**
	 * Return cleaned argv array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encode_html_entities = true
	 * @param bool $clean_xss = false
	 * @param bool $strip_tags = false
	 * @return mixed
	 */
	public function argv($item = NULL, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
		return $this->get_globals('argv', $item, $encode_html_entities, $clean_xss, $strip_tags);
	} // end func server
	
} /* End class request */