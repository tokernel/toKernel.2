<?php
/**
 * toKernel - Universal PHP Framework.
 * Main application class for working with command line interface.
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
 * @version    1.2.1
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
	 * Main function for application.
	 * This function calling from tokernel.inc.php file at once, and
	 * call the action function of requested addon prefixed by 'cli_'.
	 * Second time calling this function from any part of application
	 * will generate error.
	 *
	 * @final
	 * @access public
	 * @return bool
	 */
	final public function run() {
		
		/* Generating error if called this function at second time */
		if(self::$runned) {
			trigger_error('Application is already runned. '.__CLASS__.'::'.__FUNCTION__.'()', E_USER_ERROR);
		}
		
		tk_e::log_debug('Start', 'app->'.__FUNCTION__);
		
		/* Set id_addon and action to call */
		$id_addon = $this->request->addon();
		$action = $this->request->action();
		
		/* Define hooks object */
		$this->hooks = new hooks();
		
		tk_e::log_debug('Loaded "hooks" object', 'app->'.__FUNCTION__);
		
		/* Call first hook before main addon call */
		if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
			tk_e::log_debug('Running application hooks (before)', 'app->'.__FUNCTION__);
			$this->hooks->before_run();
		}
		
		/* Call second hook before main addon call */
		if($this->config->item_get('allow_cli_hooks', 'CLI') == 1) {
			tk_e::log_debug('Running CLI hooks (before)', 'app->'.__FUNCTION__);
			$this->hooks->cli_before_run();
		}
		
		/*
		 * Check, is addon exists
		 */
		if($this->addons->exist($id_addon) == false) {
			
			tk_e::log('Addon `'.$id_addon.'` not exists!', E_USER_NOTICE,
				__FILE__, __LINE__);
			
			$this->response->output_usage('Addon `'.$id_addon.'` not exists!');
			exit(1);
		}
		
		/* Load object for requested addon */
		$addon = $this->addons->load($id_addon);
		
		/* Check, is addon is object */
		if(!is_object($addon)) {
			
			tk_e::log('Addon `'.$id_addon.'` exists but not an object!',
				E_USER_ERROR, __FILE__, __LINE__);
			
			$this->response->output_usage('Addon `'.$id_addon.'` not exists!');
			exit(1);
		}
		
		/* Check, is addon allowed under current run mode */
		if($addon->config('allow_cli', 'CORE') != '1') {
			trigger_error('Cannot call Addon "'.$id_addon.'" in CLI mode!',
				E_USER_ERROR);
		}
		
		/*
		 * Check, is requested action of addon exist for calling.
		 * Else, check, is index (default) action exist.
		 * If no actions detected, then output | generate error.
		 */
		$function_to_call = 'cli_'.$action;
		
		if(method_exists($addon, $function_to_call) == false) {
			tk_e::log("Action '" . $action."' of addon '" .
				$id_addon."' not exists!", E_USER_NOTICE, __FILE__, __LINE__);
			
			$this->response->output_usage("Action '" . $action."' of addon '" . $id_addon . "' not exists!");
			exit(1);
		}
		
		tk_e::log_debug('Call addon\'s action - "' .
			$addon->id() . "->" . $function_to_call . '"',
			'app->'.__FUNCTION__);
		
		/* Call requested action method of loaded addon */
		$addon->$function_to_call($this->request->params());
		// call_user_func_array(array($addon, $function_to_call), $this->params);
		
		unset($function_to_call);
		
		/* Call last hook after main addon call for cli */
		if($this->config->item_get('allow_cli_hooks', 'CLI') == 1) {
			tk_e::log_debug('Running CLI hooks (after)', 'app->'.__FUNCTION__);
			$this->hooks->cli_after_run();
		}
		
		/* Call last hook after main addon call */
		if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
			tk_e::log_debug('Running application hooks (after)', 'app->'.__FUNCTION__);
			$this->hooks->after_run();
		}
		
		/* Application initialized successfully */
		self::$runned = true;
		
		tk_e::log_debug('End', 'app->'.__FUNCTION__);
		
		return true;
	} // end func run
	
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
	 * Return Application allowed languages for CLI mode
	 *
	 * @access public
	 * @param string $lp
	 * @return array
	 */
	public function allowed_languages() {
		
		return array(
			'en' => 'English'
		);
		
	} // end func allowed_languages
		
} /* End of class app */