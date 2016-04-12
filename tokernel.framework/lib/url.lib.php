<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for parsing and working with URL
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
 * @version    3.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * url_lib class library 
 * 
 * @author David A. <tokernel@gmail.com>
 */
class url_lib {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Status of initialization
 * 
 * @access protected
 * @staticvar bool
 */ 
 protected static $initialized;

	protected $language_prefix = 'en';
	protected $language_parsing = false;
	protected $mode = false;
	protected $backend_dir;
	protected $params = array();
	protected $parts = array();
	protected $addon;
	protected $action;

	protected $base_url;
	protected $query_string;

/**
 * Class constructor
 *
 * @access public
 * @return void
 */ 
 public function  __construct() {

	$this->lib = lib::instance();

	self::$initialized = false;

	$this->base_url = $this->dynamic_url();
 
 } // end constructr

	public function init($config) {

		/* Return true if already initialized */
		if(self::$initialized == true) {
			trigger_error('URL initialization - ' . __CLASS__ . '->' .
				__FUNCTION__ . '() is already initialized!', E_USER_WARNING);

			return true;
		}

		$allowed_languages = explode('|', $config->item_get('http_allowed_languages', 'HTTP'));

		// For the first, set default language
		$language_prefix = $config->item_get('http_default_language', 'HTTP');

		// Try to set language from browser if allowed
		if($config->item_get('http_catch_browser_language', 'HTTP') == 1) {
			$language_prefix = $this->matches_browser_language($allowed_languages);
		}

		$this->language_parsing = $config->item_get('http_parse_language', 'HTTP');

		if(!$_SERVER['QUERY_STRING']) {

			$this->language_prefix = $language_prefix;
			$this->mode = TK_FRONTEND;
			$this->backend_dir = '';
			$this->addon = $config->item_get('frontend_default_callable_addon', 'HTTP');
			$this->action = $config->item_get('frontend_default_callable_action', 'HTTP');
			$this->params = array();
			$this->parts = array();

			self::$initialized = true;
			return true;

		}

		$_SERVER['QUERY_STRING'] = str_replace(
			$config->item_get('http_get_var', 'HTTP') . '=',
			'',
			$_SERVER['QUERY_STRING']
		);

		$_SERVER['QUERY_STRING'] = trim($_SERVER['QUERY_STRING'], '/');

		$this->query_string = $_SERVER['QUERY_STRING'];

		$params = explode('/', $_SERVER['QUERY_STRING']);

		$this->parts = $params;

		$mode = TK_FRONTEND;
		$backend_dir = '';

		// Detect language prefix from url if allowed
		if(!empty($params) and $config->item_get('http_parse_language', 'HTTP') == 1) {
			if(in_array($params[0], $allowed_languages)) {
				$language_prefix = array_shift($params);
			}
		}

		// Detect, if accessed to backend
		if(!empty($params)) {

			if($config->item_get('backend_dir', 'HTTP') == $params[0]) {
				$backend_dir = array_shift($params);
				$mode = TK_BACKEND;
				$this->mode = TK_BACKEND;
			}

		}

		// Parse routing
		if(!empty($params)) {

			$params = routing::parse($params);

			$this->addon = array_shift($params);

			if(!empty($params)) {
				$this->action = array_shift($params);
			} else {
				$this->action = $config->item_get($mode.'_default_callable_action', 'HTTP');
				/*
				if($mode == TK_FRONTEND) {
					$this->action = $config->item_get('frontend_default_callable_action', 'HTTP');
				} else {
					$this->action = $config->item_get('backend_default_callable_action', 'HTTP');
				}
				*/
			}

		} else {

			$this->addon = $config->item_get($mode.'_default_callable_addon', 'HTTP');
			$this->action = $config->item_get($mode.'_default_callable_action', 'HTTP');

			/*
			if($mode == TK_FRONTEND) {
				$this->addon = $config->item_get('frontend_default_callable_addon', 'HTTP');
				$this->action = $config->item_get('frontend_default_callable_action', 'HTTP');
			} else {
				$this->addon = $config->item_get('backend_default_callable_addon', 'HTTP');
				$this->action = $config->item_get('backend_default_callable_action', 'HTTP');
			}
			*/
		}

		$this->language_prefix = $language_prefix;
		$this->mode = $mode;
		$this->backend_dir = $backend_dir;
		$this->params = $params;

		self::$initialized = true;

		return true;

	}

public function subdomain() {


	$tmp = explode('.', $_SERVER['HTTP_HOST']);

	if(count($tmp) == 3) {
		return $tmp[0];
	}

	return false;

}

public function language_prefix() {
	return $this->language_prefix;
}

public function language_parsing() {
	return $this->language_parsing;
}

public function mode() {
	return $this->mode;
}

public function backend_dir() {
	return $this->backend_dir;
}

	/**
	 * Return base url
	 *
	 * @access public
	 * @return string
	 */
	public function base_url() {
		return $this->base_url;
	}

public function addon() {
	return $this->addon;
}

public function action() {
	return $this->action;
}

	/**
	 * Return query string
	 *
	 * @access public
	 * @return string
	 */
	public function query_string() {
		return $this->query_string;
	}

	/**
	 * Return true if the request protocol is https
	 *
	 * @access public
	 * @return bool
	 * @since version 2.5.0
	 */
	public function is_https() {

		if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
			return true;
		} else {
			return false;
		}

	} // End func is_https

	/**
	 * Generate and return server url
	 *
	 * @access public
	 * @return string
	 */
	public function dynamic_url() {

		$base_url = '';

		if($this->is_https()) {
			$base_url .= 'https://';
		} else {
			$base_url .= 'http://';
		}

		$base_url .= $_SERVER['HTTP_HOST'];

		return $base_url;

	} // end func dynamic_url

	/**
	 * Return exploded parts from url
	 *
	 * @access public
	 * @param int $index
	 * @return mixed
	 * @since version 2.3.0
	 */
	public function parts($index = NULL) {

		if(is_null($index)) {
			return $this->parts;
		}

		if(isset($this->parts[$index])) {
			return $this->parts[$index];
		}

		return false;

	} // End func parts

	/**
	 * Return count of url parts
	 *
	 * @access public
	 * @return integer
	 * @since version 2.3.0
	 */
	public function parts_count() {
		return count($this->parts);
	}

	/**
	 * Return parameter value by item or parameters array
	 *
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public function params($item = NULL) {

		if(is_null($item)) {
			return $this->params;
		}

		if(isset($this->params[$item])) {
			return $this->params[$item];
		}

		return false;

	} // end func params

	/**
	 * Return count of parameters
	 *
	 * @access public
	 * @return integer
	 */
	public function params_count() {
		return count($this->params);
	}

/**
 * Check, if the browser languages allowed for application.
 * 
 * @access protected
 * @param array $allowed_languages
 * @return string | bool
 * @since v.2.1.0
 */ 
 protected function matches_browser_language($allowed_languages) {

	 $b_languages = $this->lib->client->languages();
	 
	 if(count($b_languages) == 0) {
		 return false;
	 }

	 foreach($b_languages as $language) {
		
		$tmp = explode('-', $language);
		$prefix = $tmp[0];
		 
		if(in_array($prefix, $allowed_languages)) {
			return $prefix;
		}

	 }
	 
	 return false;
			
 } // End func matches_browser_language

/**
 * Return url
 * 
 * @access public
 * @param mixed $id_addon
 * @param mixed $action
 * @param mixed $params_set
 * @return string
 */ 
 public function url($id_addon = NULL, $action = NULL, $params_set = NULL) {
 	
 	/* Set base url */
 	$url = $this->base_url();

	/* Append language prefix if enabled */
	if($this->language_parsing() == true) {
		$url .= '/' . $this->language_prefix();
	}

 	/*
 	 * Append backend dir (administrator control panel name)
 	 * if in backend mode, and backend_dir is not empty
 	 */
 	if($this->mode() == TK_BACKEND and $this->backend_dir() != '') {
 		$url .= '/' . $this->backend_dir();
 	}

 	/* Append id_addon to url */
 	if($id_addon === true) {
 		$url .= '/' . $this->addon();
 	} elseif(trim($id_addon) != '') {
 		$url .= '/' . $id_addon;
 	}
 	
 	/* Append action to url */
 	if($action === true) {
 		$url .= '/' . $this->action();
 	} elseif(trim($action) != '') {
 		$url .= '/' . $action;
 	}
 	
 	$params_arr = array();
 	
 	/* Append parameters */
 	if($params_set === true) {
 		$params_arr = $this->params();
 	} elseif(!empty($params_set) and is_array($params_set)) {
	    $params_arr = $params_set;
    }

 	if(count($params_arr) > 0) {
 		foreach($params_arr as $param) {
 			$url .= '/' . $param;
 		}
 	}

	// Remove last slash
	$url = rtrim($url, '/');

 	return $url;
 	
 } // end func url

/**
 * Set language prefix
 * 
 * @access public
 * @param string $lp
 * @return void
 */
 public function set_language_prefix($lp) {
 	$this->language_prefix = $lp;
 }
 
/**
 * Set addon
 * 
 * @access public
 * @param string $addon
 * @return bool
 */
 public function set_addon($addon) {
 	
 	if(trim($addon) != '') {
 		$this->addon = $addon;
 		return true;
 	}
 	
 	return false;
	
 } // end func set_addon
 
/**
 * Set action
 * 
 * @access public
 * @param string $action
 * @return bool
 */
 public function set_action($action) {
 	
 	if(trim($action) != '') {
 		$this->action = $action;
 		return true;
 	}

 	return false;
	
 } // end func set_action
 
/**
 * Set application mode
 * 
 * @access public
 * @param string $mode 'frontend' | 'backend'
 * @return void
 */ 
 public function set_mode($mode) {

	if($mode != TK_BACKEND and $mode != TK_FRONTEND) {
 		trigger_error('Can set application mode only as `' . TK_BACKEND . 
 						'` or `'.TK_FRONTEND.'` !', E_USER_ERROR);
 	}
 		
 	$this->mode = $mode;
 }

/* End of class url_lib */
}

/* End of file */
?>