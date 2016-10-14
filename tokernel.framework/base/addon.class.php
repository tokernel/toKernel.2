<?php
/**
 * toKernel - Universal PHP Framework.
 * Base addon class for addons.
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
 * @category   base
 * @package    framework
 * @subpackage base
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    4.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * class addon
 *  
 * @author David A. <tokernel@gmail.com>
 */
abstract class addon {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;

/**
 * Main Addons object for accessing all addons
 *
 * @var object
 * @access protected
 */
 protected $addons;
   
/**
 * Addon id
 * 
 * @access protected
 * @var string
 */
 protected $id;

/**
 * Addon configuration object
 * 
 * @access protected
 * @var object
 */ 
 protected $config;
 
/**
 * Addon log instance
 * 
 * @var object
 * @access protected
 */ 
 protected $log; 

/**
 * Addon status
 * Loaded from framework or application addons directory
 * 
 * @access protected
 * @var bool
 */ 
 protected $loaded_from_app_path;
  
/**
 * Class construcor
 * 
 * @access public
 * @param array $params = array()
 * @return void
 */
 public function __construct($params = array()) {

	// Set main objects
 	$this->lib = lib::instance();
	$this->app = app::instance();
	$this->addons = addons::instance();

	// Set special parameter
	$this->config = $params['~config'];
	$this->log = $params['~log'];
	$this->id = $params['~id'];

	// Unset special parameters from temporary
	unset($params['~config']);
	unset($params['~log']);
	unset($params['~id']);

	// Set parameters
 	$this->params = $params;

 	/* Define loaded path */
 	$app_addon_lib = TK_APP_PATH . 'addons' . TK_DS .
	                                  $this->id . TK_DS . 'lib' . TK_DS .  
	                                  $this->id . '.addon.php';
	if(is_file($app_addon_lib)) {
		$this->loaded_from_app_path = true;
	} else {
		$this->loaded_from_app_path = false;
	}

 } // end constructor

/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
 	unset($this->config);
 	unset($this->log);
 	unset($this->params);
 } // end destructor

/**
 * Load and return this addon's module object.
 *
 * The Module loading logic.
 *
 * Case 1.
 *      Module exists in framework and not exists in app:
 *      Load only from framework.
 *
 * Case 2.
 *      Module exists in app and not exists in framework:
 *      Load only from app.
 *
 * Case 3.
 *      Module exists in both: app and framework:
 *      Load only from app (override framework).
 *
 * Case 4.
 *      Loaded module from app or from framework,
 *      but the extended file exists in app.
 *      Example:
 *          Module file: /framework/addons/user/modules/manage_profile.module.php
 *          Module class: class user_manage_profile_module extends module
 *          Extended file: /app/addons/user/modules/manage_profile.ext.module.php
 *          Extended class: class user_manage_profile_ext_module extends user_manage_profile_module
 *
 *      So, Extended module will be loaded and inherit the parent module file.
 *      Note: There are no difference if parent module file loaded from framework or app.
 *
 * If argument $clone is true, then this will clone and return new object
 * of module. Else, the module object will returned from loaded modules.
 * 
 * @final
 * @access public
 * @param string $id_module
 * @param mixed $params
 * @param bool $clone
 * @return object
 * @since 4.0.0
 */
 final public function load_module($id_module, $params = array(), $clone = false) {

	return $this->addons->load_module($this->id, $id_module, $params, $clone);

} // end func load_module

/**
 * Load and return this addon's model object.
 *
 * The Model loading logic.
 *
 * Case 1.
 *      Model exists in framework and not exists in app:
 *      Load only from framework.
 *
 * Case 2.
 *      Model exists in app and not exists in framework:
 *      Load only from app.
 *
 * Case 3.
 *      Model exists in both: app and framework:
 *      Load only from app (override framework).
 *
 * Case 4.
 *      Loaded Model from app or from framework,
 *      but the extended file exists in app.
 *      Example:
 *          Model file: /framework/addons/user/models/manage.model.php
 *          Model class: class user_manage_model extends model
 *          Extended file: /app/addons/user/models/manage.ext.model.php
 *          Extended class: class user_manage_ext_model extends user_manage_model
 *
 *      So, Extended model will be loaded and inherit the parent model file.
 *      Note: There are no difference if parent model file loaded from framework or app.
 *
 * If argument $clone is true, then this will clone and return new object
 * of model. Else, the model object will returned from loaded models.
 *
 * @final
 * @access public
 * @param string $id_model
 * @param mixed $instance
 * @param bool $clone
 * @return object
 * @since 4.0.0
 */
 final public function load_model($id_model, $instance = NULL, $clone = false) {
	return $this->addons->load_model($this->id, $id_model, $instance, $clone);
 } // end func load_model

/**
 * Load view file for addon and return 'view' object.
 * Include view file from application dir if exists, 
 * else include from framework dir. Return false, if 
 * view file not exists in both directories. 
 *
 * @final
 * @access public
 * @param string $file
 * @param array $vars = array()
 * @return mixed string | false
 * @since 2.0.0
 */
 final public function load_view($file, $vars = array()) {
	return $this->addons->load_view($this->id, NULL, $file, $vars);
 } // end func load_view

/**
 * Return Module path if module exists in framework or app.
 * If File exists in both, the function will return path from app.
 * Return false if module not exists.
 * 
 * @access public
 * @param string $id_module
 * @return mixed
 * @since 4.0.0
 */ 
 public function module_exists($id_module) {
	 return $this->addons->module_exists($this->id, $id_module);
 } // end func module exists

/**
 * Return Model path if model exists in framework or app.
 * If File exists in both, the function will return path from app.
 * Return false if model not exists.
 *
 * @access public
 * @param string $id_model
 * @return mixed
 * @since 4.0.0
 */
 public function model_exists($id_model) {
	return $this->addons->model_exists($this->id, $id_model);
 } // end func model exists
 
/**
 * Return addon configuration values.
 * Return config array if item is null nad 
 * section defined, else, return value by item
 * 
 * @final
 * @access public
 * @param string $item
 * @param string $section
 * @return mixed
 */   
 final public function config($item = NULL, $section = NULL) {

 	if(isset($item)) {
 		return $this->config->item_get($item, $section);
 	}
 	
 	if(!isset($item) and isset($section)) {
 		return $this->config->section_get($section); 
 	}
 	
 	return false;

 } // end func config
   
/**
 * Return this addon id
 * 
 * @final
 * @access public
 * @return string
 */
 final public function id() {
	return $this->id;
 }

/**
 * Get language value by expression
 * Return language prefix if item is null.
 *
 * NOTE: From toKernel version 2.0.0 Languages supported only by application.
 * Addons not supporting own language files.
 *  
 * @access public
 * @param string $item
 * @return string
 */ 
 public function language($item) {
 	
 	if(func_num_args() > 1) {
 		$l_args = func_get_args();
 	
 		unset($l_args[0]);
 		
 		if(is_array($l_args[1])) {
 			$l_args = $l_args[1];
 		}
 		
 		return $this->app->language($item, $l_args);
 	}
 	
 	return $this->app->language($item);
 	
 } // end func language

/**
 * Return addon's url by loaded stage
 * Detect if addon loaded from application directory or from framework.
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function url() {
 	if($this->loaded_from_app_path == true) {
 		return $this->app_url();
 	} else {
 		return $this->tk_url();
 	}
 }
 
/**
 * Return addon's url from application path
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function app_url() {
 	if(TK_APP_DIR != '') {
 		return $this->lib->url->base_url() . '/' . TK_APP_DIR . '/addons/' . $this->id;
 	} else {
 		return $this->lib->url->base_url() . '/' . TK_DIR . '/addons/' . $this->id;
 	}
 } 

/**
 * Return addon's url from framework path
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function tk_url() {
 	return $this->lib->url->base_url() . TK_DIR . '/addons/' . $this->id;
 }

/**
 * Return addon's path by loaded stage
 * Detect if addon loaded from application directory or from framework.
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function path() {
 	if($this->loaded_from_app_path == true) {
 		return $this->app_path();
 	} else {
 		return $this->tk_path();
 	}
 }
 
/**
 * Return addon's path from application directory
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function app_path() {
 	if(TK_APP_DIR != '') {
 		return TK_ROOT_PATH . TK_APP_DIR . TK_DS .
 							'addons' . TK_DS . $this->id . TK_DS;
 	} else {
 		return TK_ROOT_PATH . 'addons' . TK_DS . $this->id . TK_DS;
 	}
 } 

/**
 * Return addon's path from framework directory
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function tk_path() {
 	return TK_PATH . 'addons' . TK_DS . $this->id . TK_DS;
 }
  
/**
 * Return true if addon loaded from application directory
 * 
 * @access public
 * @return bool
 * @since 3.2.0
 */
 public function loaded_from_app_path() { 
	return $this->loaded_from_app_path;
 }
 	 
/**
 * Return true if addon called from backend url or 
 * backend_dir is empty (not set) in configuration. 
 * Else, return false.  
 * 
 * @access public
 * @return bool
 * @since 2.2.0
 */ 
 public function is_backend() {
 	if($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
		return false;
	} else {
		return true;
	}
 } 

/**
 * Return true if addon's action called from backend url
 * or backend_dir is empty (not set) in configuration. 
 * Else, redirect to error_404  
 * 
 * @access public
 * @return bool
 * @since 2.2.0
 */
 public function check_backend() {

	if($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
 		$this->app->error_404('Cannot call method of class `' .	get_class($this).'` by this url.');
 		return false;
 	}
 	
 	return true;
 }
 
/**
 * Exception for not creating function 
 * 'action_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function action_() {}

/**
 * Exception for not creating function 
 * 'action_ax_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function action_ax_() {}
 
/**
 * Exception for not creating function 
 * 'cli_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function cli_() {}

/* End of class addon */
}
 
/* End of file */
?>