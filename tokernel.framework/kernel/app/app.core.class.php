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
	 * Constructor is final and protected for singleton instance
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
		unset(self::$instance->language);
		unset(self::$instance->hooks);
		unset(self::$instance->lib);
		unset(self::$instance->request);
		unset(self::$instance->response);
		unset(self::$instance->addons);
	} // end func _destruct
	
	/**
	 * Return Application Singleton object.
	 * Initialize Application core in first call.
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
		
		/* Initialize Application object. */
		$obj = 'app';
		self::$instance = new $obj;
		
		/* Load library loader object */
		self::$instance->lib = lib::instance();
		
		/* Load request object */
		self::$instance->request = request::instance();
		
		/* Load response object */
		self::$instance->response = response::instance();
		
		/* Load addons loader object */
		self::$instance->addons = addons::instance();
		
		/* Load Application configuration */
		self::$instance->config = self::$instance->lib->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'application.ini');
				
		/* Set error reporting by application run mode */
		if(self::$instance->config->item_get('app_mode', 'RUN_MODE') == 'production') {
			error_reporting(E_ALL & ~E_NOTICE);
		} else {
			error_reporting(E_ALL);
		}
				
		/* Initialization by application mode specific. */
		if(TK_RUN_MODE == TK_HTTP_MODE) {
			
			tk_e::log_debug('Running in HTTP mode', 'app::' . __FUNCTION__);
			
			/* Check, if http mode not allowed */
			if(self::$instance->config->item_get('allow_http', 'HTTP') != 1) {
				
				tk_e::log_debug('HTTP mode not allowed', 'app::' . __FUNCTION__);
				
				throw new Exception('toKernel - Universal PHP Framework v' . TK_VERSION . '. HTTP mode not allowed.', E_USER_ERROR);
				
			}
			
			/* Initialize Request */
			/* Get Parsed configuration of HTTP Interface and merge with HTTP Configuration */
			self::$instance->config->section_set(
				'HTTP',
				self::$instance->request->init(self::$instance->config)
			);
			
			/* @todo check if under maintenance */
									
		} elseif(TK_RUN_MODE == TK_CLI_MODE) {
			
			tk_e::log_debug('Running in CLI mode', 'app::' . __FUNCTION__);
			
			/* Check, is cli mode allowed */
			if(self::$instance->config->item_get('allow_cli', 'CLI') != 1) {
				
				tk_e::log_debug('CLI mode not allowed', 'app::' . __FUNCTION__);
				
				throw new Exception('toKernel - Universal PHP Framework v' . TK_VERSION . '. CLI mode not allowed.', E_USER_ERROR);
			}
			
			/* Initialize Request */
			self::$instance->request->init($argv, self::$instance->config);
						
		} // end run mode
		
		$language_prefix = self::$instance->request->language_prefix();
				
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
	 * Return application configuration array if item
	 * is NULL. Else, return config value by item.
	 *
	 * @access public
	 * @param string $item
	 * @param mixed $section = NULL
	 * @return mixed
	 */
	public function config($item, $section = NULL) {
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
			
} /* End of class app_core */