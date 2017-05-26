<?php
/**
 * toKernel - Universal PHP Framework.
 * Base application class.
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
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * app_core class
 *
 * @author David A. <tokernel@gmail.com>
 */
abstract class app_core {
	
	/**
	 * Status of application class instance
	 *
	 * @staticvar object
	 * @access protected
	 */
	protected static $instance;
	
	/**
	 * Instance of this object
	 * will be defined at once
	 *
	 * @staticvar bool
	 * @access protected
	 */
	protected static $initialized = false;
	
	/**
	 * Status of application run
	 * will defined in child class init method.
	 *
	 * @staticvar bool
	 * @access protected
	 */
	protected static $runned = false;
	
	/**
	 * Request class library
	 *
	 * @var    object
	 * @access protected
	 * @since  Version 1.2.0
	 */
	protected $request;
	
	/**
	 * Response class library
	 *
	 * @var    object
	 * @access protected
	 * @since  Version 1.2.0
	 */
	protected $response;
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;
	
	/**
	 * Application configuration object
	 *
	 * @var object
	 * @access protected
	 */
	protected $config;
	
	/**
	 * Application log instance
	 *
	 * @var object
	 * @access protected
	 */
	protected $log;
	
	/**
	 * Language object for application
	 *
	 * @var object
	 * @access protected
	 */
	protected $language;
	
	/**
	 * Hooks object for application
	 *
	 * @var object
	 * @access protected
	 */
	protected $hooks;
	
	/**
	 * Constructor is final and protected for singlton instance
	 *
	 * @final
	 * @access protected
	 */
	final protected function __construct() {}
	
	/**
	 * Class destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		unset(self::$instance->config);
		unset(self::$instance->log);
		unset(self::$instance->language);
		unset(self::$instance->request);
		unset(self::$instance->response);
	} // end func _destruct
	
	/**
	 * Singleton function to return one instance of this class.
	 *
	 * @final
	 * @throws Exception
	 * @access public
	 * @static
	 * @param mixed $argv = NULL
	 * @return object
	 */
	final public static function instance($argv = NULL) {
		
		/* Check, is instance initialized */
		if(isset(self::$instance)) {
			return self::$instance;
		}
		
		tk_e::log_debug('Start', 'app::' . __FUNCTION__);
		
		/*
		 * Set name of child class and
		 * initialize instance object.
		 */
		$obj = 'app';
		self::$instance = new $obj;
		
		/* Load library object */
		self::$instance->lib = lib::instance();
		
		/* Load request object */
		self::$instance->request = request::instance();
		
		/* Load response object */
		self::$instance->response = response::instance();
		
		/* Load addons object */
		self::$instance->addons = addons::instance();
		
		/* Load configuration */
		self::$instance->config = self::$instance->lib->ini->instance(
			TK_APP_PATH . 'config' . TK_DS . 'application.ini');
		
		tk_e::log_debug('Loaded "config" object', 'app::' . __FUNCTION__);
		
		if(!self::$instance->config) {
			throw new Exception('toKernel - Universal PHP Framework v' . TK_VERSION .
				'. Application configuration file is not readable.', E_USER_ERROR);
		}
		
		/* Set error reporting by application mode */
		if(self::$instance->config->item_get('app_mode', 'RUN_MODE') == 'production') {
			error_reporting(E_ALL & ~E_NOTICE); // E_ALL ^ E_NOTICE
		} else {
			error_reporting(E_ALL);
		}
		
		/* Load log instance */
		self::$instance->log = self::$instance->lib->log->instance('application.log');
		tk_e::log_debug('Loaded "log" object', 'app::' . __FUNCTION__);
		
		/* Initialization by application mode */
		if(TK_RUN_MODE == 'http') {
			
			tk_e::log_debug('Running in HTTP mode', 'app::' . __FUNCTION__);
			
			/* Check, is http mode allowed */
			if(self::$instance->config->item_get('allow_http', 'HTTP') != 1) {
				
				tk_e::log_debug('HTTP mode not allowed', 'app::' . __FUNCTION__);
				
				throw new Exception('toKernel - Universal PHP Framework v' . TK_VERSION . '. HTTP mode not allowed.', E_USER_ERROR);
				
			}
			
			/* Initialize Request */
			self::$instance->request->init(self::$instance->config);
						
		} elseif(TK_RUN_MODE == 'cli') {
			
			tk_e::log_debug('Running in CLI mode', 'app::' . __FUNCTION__);
			
			/* Check, is cli mode allowed */
			if(self::$instance->config->item_get('allow_cli', 'CLI') != 1) {
				
				tk_e::log_debug('CLI mode not allowed', 'app::' . __FUNCTION__);
				
				throw new tk_e('toKernel - Universal PHP Framework v' . TK_VERSION
					. '. CLI mode not allowed.', E_USER_ERROR);
			}
									
			/* Initialize Request */
			self::$instance->config->section_set(
				'HTTP_INTERFACE',
				self::$instance->request->init($argv, self::$instance->config)
			);
						
		} // end run mode
		
		$language_prefix = self::$instance->request->language_prefix();
		
		if($language_prefix == '') {
			tk_e::log_debug('Language prefix is invalid. Set to default "en"', 'app::' . __FUNCTION__);
			$language_prefix = 'en';
		}
		
		/* Set timezone for application */
		ini_set('date.timezone', self::$instance->config->item_get('date_timezone', 'APPLICATION'));
		
		/* Set internal character encoding to UTF-8 */
		mb_internal_encoding(self::$instance->config->item_get('app_charset', 'APPLICATION'));
		
		/* Load language object for application */
		self::$instance->language = self::$instance->lib->language->instance(TK_APP_PATH . 'languages' . TK_DS . $language_prefix . '.ini');
		
		tk_e::log_debug('Loaded "language" object', 'app::' . __FUNCTION__);
		
		/* Configure error handler with application configuration values */
		self::$instance->config->item_set(
			'err_subject_production',
			self::$instance->language->get('err_subject_production'),
			'ERROR_HANDLING'
		);
		
		self::$instance->config->item_set(
			'err_message_production',
			self::$instance->language->get('err_message_production'),
			'ERROR_HANDLING'
		);
		
		self::$instance->config->item_set(
			'err_404_subject',
			self::$instance->language->get('err_404_subject'),
			'ERROR_HANDLING'
		);
		
		self::$instance->config->item_set(
			'err_404_message',
			self::$instance->language->get('err_404_message'),
			'ERROR_HANDLING'
		);
		
		tk_e::configure_error_handling(self::$instance->config->section_get('ERROR_HANDLING'));
		
		tk_e::log_debug('Configured Error Exception/Handler data', 'app::' . __FUNCTION__);
		
		/* Set initialization status variables */
		self::$initialized = true;
		self::$runned = false;
		
		tk_e::log_debug('End', 'app::' . __FUNCTION__);
		
		return self::$instance;
		
	} // end func instance
	
	/**
	 * Dissable clone of this object
	 *
	 * @final
	 * @access public
	 * @return void
	 */
	final public function __clone() {
		trigger_error('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
		
	} // end func __clone
	
	/**
	 * Return application init status
	 *
	 * @access public
	 * @return bool
	 */
	public static function initialized() {
		return self::$initialized;
	}
	
	/**
	 * Return application run status
	 *
	 * @access public
	 * @return bool
	 */
	public static function runned() {
		return self::$runned;
	}
	
	/**
	 * Error function, will call tk_e::error()
	 *
	 * @access public
	 * @param integer $code
	 * @param string $message
	 * @param string $file
	 * @param integer $line
	 * @return void
	 */
	public function error($code, $message, $file = NULL, $line = NULL) {
		
		if(is_null($file)) {
			$file = __FILE__;
		}
		
		if(is_null($line)) {
			$line = __LINE__;
		}
		
		tk_e::error($code, $message, $file, $line);
		
	} // end func error
	
	/**
	 * Return application configuration array if item
	 * is NULL. Else, return config value by item.
	 *
	 * @access public
	 * @param string $item
	 * @param mixed $section = NULL
	 * @return mixed
	 */
	public function config($item, $section = NULL) {
		
		if(!isset(self::$instance)) {
			trigger_error('Application is not initialized. Instance is empty (' . __CLASS__.')',  E_USER_ERROR );
		}
		
		return $this->config->item_get($item, $section);
		
	} // end func config
	
	/**
	 * Abstract function for childs
	 *
	 * @access public
	 * @abstract
	 * @return void
	 */
	abstract public function run();
	
	/**
	 * Return Timezones from configuration file.
	 * return timezone by section name is set.
	 *
	 * @access public
	 * @param  string $section
	 * @return array
	 * @since  1.1.0
	 */
	public function timezones($section = NULL) {
		
		if(!isset(self::$instance)) {
			trigger_error('Application instance is empty ('.__CLASS__.')',
				E_USER_ERROR );
		}
		
		$tk_timezone_file = TK_PATH . 'config' . TK_DS . 'timezones.ini';
		$app_timezone_file = TK_APP_PATH . 'config' . TK_DS . 'timezones.ini';
		
		if(is_readable($app_timezone_file)) {
			$timezone_file = $app_timezone_file;
		} elseif(is_readable($tk_timezone_file)) {
			$timezone_file = $tk_timezone_file;
		} else {
			trigger_error('File `'.$tk_timezone_file.'` not exists', E_USER_ERROR);
			return false;
		}
		
		$data_arr = array();
		
		if(!is_null($section)) {
			$ini = $this->lib->ini->instance($timezone_file, $section, false);
			$data_arr = $ini->section_get($section);
		} else {
			$ini = $this->lib->ini->instance($timezone_file, NULL, false);
			$sections = $ini->sections();
			
			foreach($sections as $section) {
				$data_arr[$section] = $ini->section_get($section);
			}
		}
		
		return $data_arr;
		
	} // end func timezones
		
} /* End of class app_core */