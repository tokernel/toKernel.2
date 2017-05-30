<?php
/**
 * toKernel - Universal PHP Framework.
 * Main application class for working with http mode.
 * Child of app_core class.
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
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * app class
 *
 * @author David A. <tokernel@gmail.com>
 */
class app extends app_core {
			
	/**
	 * Initialized Template file name of main
	 * callable addon without '*.tpl.php' extension.
	 *
	 * @access private
	 * @var string
	 */
	private $template;
	
	/**
	 * Template parsing variables
	 *
	 * @access private
	 * @var array
	 */
	private $template_vars = array();
	
	/**
	 * Is output buffer from cache.
	 * This variable will true, if content loaded from cache.
	 *
	 * @access private
	 * @var bool
	 */
	private $is_output_content_cached = false;
	
	
	/**
	 * Global variables to use in templates and view files.
	 * Example: {var.base_url} will convert to application base url.
	 *
	 * @access private
	 * @var array
	 */
	private $variables = array();
	
	/**
	 * Main function for application.
	 * This function calling from tokernel.inc.php file at once, and
	 * call the action function of requested addon prefixed by 'action_'.
	 * Second time calling this function from any part of application
	 * will generate error.
	 *
	 * @final
	 * @access public
	 * @throws ErrorException
	 * @return bool
	 */
	final public function run() {
		
		/* Calling this function second time will trigger error. */
		if(self::$runned) {
			throw new ErrorException('Application is already runned. '.__CLASS__.'::'. __FUNCTION__.'()');
		}
				
		/* Define hooks object */
		$this->hooks = new hooks();
				
		/* Call first hook before main addon call */
		if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
			$this->hooks->before_run();
		}
		
		/* Call second hook before main addon call */
		if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
			$this->hooks->http_before_run();
		}
		
		/*
		 * Trying to load page content from cache.
		 * NOTICE:
		 *  Cache works only in GET request.
		 *  Cache should be configured in caching.ini file with name of HTTP Interface.
		 *  For example: Interface "ABC" should be configured in file with [ABC] Section.
		 *  If no interface Defined, the section [tokernel_default] will be used.
		 *
		 *  cache_expiration: the value -1 assume that the cache is never expired.
		 */
		$cache = $this->lib->cache->instance($this->request->interface_name());
		$ce_ = $cache->config('cache_expiration');
						
		if(($ce_ > 0 or $ce_ == '-1') and $this->request->method() == 'GET') {
						
			/*
			 * Trying to get cached content.
			 * Will return false if cache expired or not exists.
			 */
			$cached_content = $cache->get_content(
				$this->request->url(),
				$ce_
			);
			
			/*
			 * Cached content is not empty.
			 * Output content and return true.
			 */
			if($cached_content) {
				
				$this->response->set_content($cached_content);
								
				$this->is_output_content_cached = true;
				
				unset($cached_content);
				
				/* Set application run status */
				self::$runned = true;
								
				/* Output Application buffer */
				$this->response->output();
				
				/* Call Last hook after main addon call in HTTP mode */
				if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
					$this->hooks->http_after_run();
				}
				
				/* Call Last hook after main addon call */
				if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
					$this->hooks->after_run();
				}
								
				return true;
				
			} // end if content
						
		} // end checking cache_expiration
		
		/* Set id_addon for load */
		$id_addon = $this->request->addon();
		
		/* Check, if the default callable addon not exists */
		$addon_exists = $this->addons->exist($id_addon);
		
		if($addon_exists == false and $id_addon == $this->config->item_get('default_callable_addon', 'HTTP')) {
			tk_e::error(E_USER_ERROR, 'Default callable addon not exists.', __FILE__, __LINE__);
		}
		
		/* Check, if the callable addon not exists */
		if($addon_exists == false) {
			$this->error_404('Addon `'.$id_addon.'` not exists.', __FILE__, __LINE__);
		}
		
		/* Load main callable addon object */
		$addon = $this->addons->load($id_addon);
				
		/* Check, is addon allowed under current run mode */
		if($addon->config('allow_http', 'CORE') != '1') {
			trigger_error('Addon "'.$id_addon.'" cannot run under HTTP mode!',
				E_USER_ERROR);
			return false;
		}
		
		/* Set action to call */
		$action = $this->request->action();
		
		/* Check, if action with 'ax_' ajax prefix */
		if($action != '' and substr($action, 0, 3) == 'ax_') {
			$this->error_404('Action "'.$action.'" with "ax_" prefix not allowed!');
		}
		
		/* Define callable method of addon */
		if($this->request->is_ajax() == true) {
			$function_to_call = 'action_ax_' . $action;
		} else {
			$function_to_call = 'action_'.$action;
		}
		
		/* Check, is method of addon exists */
		if(!method_exists($addon, $function_to_call)) {
			
			$this->error_404('Method `'.$function_to_call.'()` in addon ' .
				'class `'.$id_addon.'` not exists.',
				__FILE__, __LINE__);
			
		}
				
		/* Set application global variables to use in templates and view files */
		$this->load_default_vars();
		
		/* Get buffered result of addon's action. */
		ob_start();
		
		/* Call adon's action */
		$addon->$function_to_call($this->request->url_params());
		
		$addon_called_func_buffer = ob_get_contents();
		ob_end_clean();
		unset($function_to_call);
				
		/*
		 * Define default template name if not set {addon}.{action}
		 */
		if($this->template == '') {
			
			$default_template = $id_addon.'.'.$action;
			
			// Set default template if exists
			if($this->lib->template->exists($default_template)) {
				$this->template = $default_template;
			}
			
		}
		
		$content_to_output = '';
		
		/*
		 * Now if template name defined, load template object instance
		 */
		if($this->template != '') {
						
			$template_obj = $this->lib->template->instance($this->template, $this->template_vars);
			$content_to_output = $template_obj->run($addon_called_func_buffer);
						
		} else {
			
			$content_to_output = $addon_called_func_buffer;
		}
		
		/* Call second hook before output buffer */
		if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
			
			// NOTICE: The hook 'http_before_output' is able to change the output buffer with your own risk.
			// See: /application/hooks/hooks.class.php
			$content_to_output = $this->hooks->http_before_output($content_to_output);
		}
		
		$this->response->set_content($content_to_output);
		
		/*
		 * Try to write content to cache.
		 */
		if(($ce_ > 0 or $ce_ == '-1') and $this->request->method() == 'GET') {
			
			$cache->write_content(
				$this->request->url(),
				$content_to_output,
				$ce_
			);
			
		} // end checking cache_expiration
		
		/* Output Application buffer */
		$this->response->output();
		
		/* Call hook after output buffer */
		if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
			
			$this->hooks->http_after_output($content_to_output);
		}
		
		/* Call last hook after main addon call in HTTP mode */
		if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
			$this->hooks->http_after_run();
		}
		
		/* Call last hook after main addon call */
		if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
			$this->hooks->after_run();
		}
		
		self::$runned = true;
				
		return true;
		
	} // end func run
		
	/**
	 * Redirect
	 *
	 * @access public
	 * @param string $url
	 * @return void
	 */
	public function redirect($url) {
		header('Location: '.$url);
		exit();
	} // end func redirect
	
	/**
	 * Set application global variable
	 *
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 * @since 1.5.0
	 */
	public function set_var($item, $value) {
		$this->variables[$item] = $value;
	} // End func set_var
	
	/**
	 * Get application global variable by name
	 *
	 * @access public
	 * @param string $item
	 * @return mixed
	 * @since 1.5.0
	 */
	public function get_var($item) {
		
		if(!isset($this->variables[$item])) {
			return false;
		}
		
		return $this->variables[$item];
		
	} // End func get_var
	
	/**
	 * Get application all global variables
	 *
	 * @access public
	 * @return mixed
	 * @since 1.5.0
	 */
	public function get_vars() {
		return $this->variables;
	} // End func set_var
		
	/**
	 * Show error 404
	 *
	 * @access public
	 * @param string $message
	 * @param string $file
	 * @param integer $line
	 */
	public function error_404($message = NULL, $file = NULL, $line = NULL) {
		
		if(is_null($message)) {
			$message = 'Called app->error_404()';
		}
		
		if(is_null($file)) {
			$file = __FILE__;
		}
		
		if(is_null($line)) {
			$line = __LINE__;
		}
		
		$remote_address = $this->request->server('REMOTE_ADDR');
		
		if($this->config->item_get('log_errors_404', 'ERROR_HANDLING') == 1) {
			$log_ext = $this->config->item_get('log_file_extension', 'ERROR_HANDLING');
			$log_obj = $this->lib->log->instance('error_404.' . $log_ext);
			$log_obj->write($message . '  CLIENT IP: ' .$remote_address . ' | URL: ' . $this->request->url() . ' | FILE: ' . $file . ' | LINE: ' . $line);
		}
		
		$this->response->set_status(404);
		
		if($this->request->is_ajax()) {
			
			tk_e::log_debug($message . ' CLIENT IP: ' .$remote_address . ' | URL: "'. $this->request->url() . '"',	'app->'.__FUNCTION__);
			tk_e::log_debug('', ':============= END WITH ERROR 404 ==============');
			
			$this->response->set_content($this->language('err_404_subject'));
			$this->response->output();
			
			exit();
		}
		
		/* Set application global variables to use in templates and view files */
		$this->load_default_vars();
		
		$this->variables['title'] = $this->language('err_404_subject');
		
		if($this->config->item_get('app_mode', 'RUN_MODE') == 'development') {
			$this->variables['message'] = $message;
		} else {
			$this->variables['message'] = $this->language('err_404_message');
		}
		
		$this->set_template('error_404', $this->variables);
						
		/*
		 * Load template object instance
		 */
		$template_obj = $this->lib->template->instance($this->template, $this->template_vars);
				
		if($template_obj) {
			$this->response->set_content($template_obj->run());
		} else {
			$this->response->set_content($this->language('err_404_subject'));
			tk_e::log_debug('Template file "'. $this->template . '" not detected',	'app->'.__FUNCTION__);
		}
		
		/* Output Application buffer */
		$this->response->output();
		
		self::$runned = true;
		
		tk_e::log_debug($message . ' CLIENT IP: ' .$remote_address . ' | URL: "'. $this->request->url() . '"',	'app->'.__FUNCTION__);
		tk_e::log_debug('', ':============= END WITH ERROR 404 ==============');
		
		exit();
		
	} // end func error_404
	
	/**
	 * Return, is output from cache
	 *
	 * @access public
	 * @return bool
	 */
	public function is_output_content_cached() {
		return $this->is_output_content_cached;
	}
			
	/**
	 * Return template of main addon.
	 *
	 * @access public
	 * @return string
	 */
	public function get_template() {
		
		if(!isset(self::$instance)) {
			trigger_error('Application instance is empty ('.__CLASS__.')',
				E_USER_ERROR );
		}
		
		return $this->template;
		
	} // end func template
	
	/**
	 * Set template for application
	 *
	 * @access public
	 * @param string $tempalte
	 * @return bool
	 */
	public function set_template($template, $template_vars = NULL) {
		
		if(!isset(self::$instance)) {
			trigger_error('Application instance is empty ('.__CLASS__.')',
				E_USER_ERROR );
		}
		
		$this->template = $template;
		$this->template_vars = $template_vars;
		
	} // end func set_template
	
	/**
	 * Return language value by item
	 * return language prefix, if item is null
	 *
	 * @access public
	 * @param string $item
	 * @param array $lng_args = array()
	 * @return string
	 */
	public function language($item, array $lng_args = array()) {
		
		if(!isset(self::$instance)) {
			trigger_error('Application instance is empty ('.__CLASS__.')', E_USER_ERROR );
		}
		
		return $this->language->get($item, $lng_args);
		
	} // end func language
	
	/**
	 * Return Application allowed languages for HTTP mode
	 *
	 * @access public
	 * @param string $lp
	 * @return array
	 */
	public function allowed_languages($lp = NULL) {
		
		$a_languages = $this->request->allowed_languages();
		
		$tk_lng_ref_file = TK_PATH . 'config' . TK_DS . 'languages.ini';
		$app_lng_ref_file = TK_APP_PATH . 'config' . TK_DS . 'languages.ini';
		
		if(is_file($app_lng_ref_file)) {
			$lng_ref = $this->lib->ini->instance($app_lng_ref_file);
		} else {
			$lng_ref = $this->lib->ini->instance($tk_lng_ref_file);
		}
		
		if(!is_null($lp)) {
			$language = array($lp => $lng_ref->item_get($lp));
			unset($a_languages);
			unset($lng_ref);
			return $language;
		}
		
		foreach($a_languages as $lng) {
			$languages[$lng] = $lng_ref->item_get($lng);
		}
		
		unset($lng_ref);
		unset($a_languages);
		
		return $languages;
		
	} // end func allowed_languages
	
	/**
	 * Set application language by prefix
	 *
	 * @access public
	 * @param string $lp
	 * @return void
	 */
	public function set_language($lp) {
		
		if(!isset(self::$instance)) {
			trigger_error('Application instance is empty ('.__CLASS__.')', E_USER_ERROR );
		}
		
		if(!$this->request->set_language_prefix($lp)) {
			trigger_error('Trying to set not allowed language prefix `'.$lp.'`.', E_USER_ERROR );
		}
		
		/* Load language object for application */
		self::$instance->language = self::$instance->lib->language->instance(TK_APP_PATH . 'languages'. TK_DS.$lp.'.ini');
		
	} // end func set_language
			
	/**
	 * Return application theme name by run mode.
	 *
	 * @access public
	 * @param mixed
	 * @return string
	 * @todo Re-build this method content
	 */
	public function theme_name($mode = NULL) {
		
		if(is_null($mode)) {
			$mode = 'some_interface';
		}
		
		return $this->config->item_get($mode.'.theme', 'HTTP');
	} // end func theme
	
	/**
	 * Return application theme path.
	 *
	 * @access public
	 * @param mixed
	 * @return string
	 * @todo rebuild this
	 */
	public function theme_path($mode = NULL) {
		
		if(is_null($mode)) {
			$mode = 'some_interface';
		}
		
		return TK_APP_PATH . 'themes' . TK_DS . $mode . TK_DS .
			$this->config->item_get($mode.'.theme', 'HTTP') . TK_DS;
		
	} // end func theme
	
	/**
	 * Return theme url
	 *
	 * @access public
	 * @param mixed
	 * @return string
	 * @todo rebuild this
	 */
	public function theme_url($mode = NULL) {
		
		if(is_null($mode)) {
			$mode = 'some_interface';
		}
		
		$url = '';
		
		if(TK_APP_DIR != '') {
			$url = $this->request->base_url() . '/' . TK_APP_DIR . '/themes/' .
				$mode . '/' .
				$this->config->item_get($mode . '.theme', 'HTTP');
		} else {
			$url = $this->request->base_url() . '/themes/' . $mode . '/' .
				$this->config->item_get($mode . '.theme', 'HTTP');
		}
		
		return $url;
		
	} // end func theme_url
	
	/**
	 * Return url of application directory
	 *
	 * @access public
	 * @return string
	 */
	public function app_url() {
		
		$url = '';
		
		if(TK_APP_DIR != '') {
			$url = $this->request->base_url() . '/' . TK_APP_DIR;
		} else {
			$url = $this->request->base_url();
		}
		
		return $url;
		
	} // end func custom_url
	
	/**
	 * Return url of framework directory path
	 *
	 * @access public
	 * @return string
	 */
	public function tk_url() {
		return $this->request->base_url() . TK_DIR;
	} // end func custom_url
	
	/**
	 * Load Default variables
	 *
	 * @access protected
	 * @return void
	 */
	protected function load_default_vars() {
		
		/* Set application global variables to use in templates and view files */
		$this->variables['base_url'] = $this->request->base_url();
		$this->variables['base_url_lng'] = $this->request->base_url() . '/' . $this->request->language_prefix();
		// @todo predefine this 2 items
		$this->variables['base_interface_url'] = $this->request->base_url() . '/' . $this->config->item_get('interface_dir', 'HTTP');
		$this->variables['base_interface_url_lng'] = $this->request->base_url() . '/' . $this->config->item_get('interface_dir', 'HTTP') . '/' . $this->request->language_prefix();
		$this->variables['app_url'] = $this->app_url();
		$this->variables['theme_name'] = $this->theme_name();
		$this->variables['theme_url'] = $this->theme_url();
		$this->variables['theme_images_url'] = $this->theme_url() . '/images';
		$this->variables['theme_js_url'] = $this->theme_url() . '/js';
		$this->variables['theme_css_url'] = $this->theme_url() . '/css';
		$this->variables['uploads_url'] = $this->request->base_url() . '/uploads';
		$this->variables['language_prefix'] = $this->request->language_prefix();
		
		/* @todo get date format from configuration */
		$this->variables['date'] = date('d/m/Y');
		$this->variables['day'] = date('d');
		$this->variables['month'] = date('m');
		$this->variables['year'] = date('Y');
		
		/* @todo also display full date/time */
		
		/* @todo also parse time, minute, second */
		
	} // End func load_default_vars
	
} /* End of class app */