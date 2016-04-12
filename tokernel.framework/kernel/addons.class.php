<?php
/**
 * toKernel - Universal PHP Framework.
 * Main addons loader singleton callable class.
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
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addons class
 *
 * @author David A. <tokernel@gmail.com>
 */
class addons {

	/**
	 * Status of this class instance
	 *
	 * @staticvar object
	 * @access private
	 */
	private static $instance;

	/**
	 * Main Application object for
	 * accessing app functions from this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $app;

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;

	/**
	 * Array of loaded addons (objects).
	 *
	 * @access protected
	 * @staticvar array
	 */
	protected static $loaded_addons = array();

	/**
	 * Array of loaded modules (objects).
	 *
	 * @access protected
	 * @staticvar array
	 */
	protected static $loaded_modules = array();

	/**
	 * Array of loaded models (objects).
	 *
	 * @access protected
	 * @staticvar array
	 */
	protected static $loaded_models = array();

	/**
	 * Array of loaded configuration objects of addons.
	 *
	 * @access protected
	 * @staticvar array
	 */
	protected static $loaded_configs = array();

	/**
	 * Array of loaded log objects of addons and modules.
	 *
	 * @access protected
	 * @staticvar array
	 */
	protected static $loaded_logs = array();

	/**
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
	 * @return void
	 */
	final private function __construct() {
		$this->app = app::instance();
		$this->lib = lib::instance();
	}

	/**
	 * Prevent cloning of the object.
	 * Trigger E_USER_ERROR if attempting to clone
	 *
	 * @access public
	 * @return void
	 */
	public function __clone() {
		trigger_error( 'Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
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
	 * This function will call 'load'
	 *
	 * @final
	 * @access public
	 * @param string $id_addon
	 * @return mixed object | bool
	 */
	final public function __get($id_addon) {
		return $this->load($id_addon);
	}

	/**
	 * Load and return addon object by id.
	 *
	 * The addon library loading logic.
	 *
	 * Case 1.
	 *      Addon lib exists in framework and not exists in app:
	 *      Load only from framework.
	 *
	 * Case 2.
	 *      Addon lib exists in app and not exists in framework:
	 *      Load only from app.
	 *
	 * Case 3.
	 *      Addon lib exists in both: app and framework:
	 *      Load only from app (override framework).
	 *
	 * Case 4.
	 *      Loaded addon from app or from framework,
	 *      but Addon extended lib file exists in app.
	 *      Example:
	 *          Addon file: /framework/addons/user/lib/user.addon.php
	 *          Addon class: class user_addon extends addon
	 *          Extended file: /app/addons/user/lib/user.ext.addon.php
	 *          Extended class: class user_ext_addon extends user_addon
	 *
	 *      So, Extended addon lib will be loaded and inherit the parent addon file.
	 *      Note: There are no difference if parent addon file loaded from framework or app.
	 *
	 * @access public
	 * @param string $id_addon
	 * @param array $params
	 * @return mixed object | bool
	 */
	public function load($id_addon, $params = array()) {

		if(trim($id_addon) == '') {
			trigger_error('Called addons->load() with empty id_addon!', E_USER_ERROR);
			return false;
		}

		// Return addon object, if it is already loaded
		if(array_key_exists($id_addon, self::$loaded_addons)) {
			return self::$loaded_addons[$id_addon];
		}

		$addon_path = $this->exist($id_addon);

		// Addon not exist
		if(!$addon_path) {
			trigger_error('Addon `' . $id_addon . '` not exists.', E_USER_ERROR);
		}

		// Load addon file
		$addon_lib_file =  $addon_path . 'lib' . TK_DS . $id_addon . '.addon.php';

		// Define addon class name
		$addon_lib_class = $id_addon . '_addon';

		require_once $addon_lib_file;

		// Define Addon extended class file name
		$addon_ext_lib_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'lib' . TK_DS . $id_addon . '.ext.addon.php';

		// Check, id addon child class exist
		if(is_file($addon_ext_lib_file)) {
			// Load extended class
			require_once $addon_ext_lib_file;
			$addon_lib_class = $id_addon . '_ext_addon';
		}

		/* Check id addon class exist */
		if(!class_exists($addon_lib_class)) {
			trigger_error('Class ' . $addon_lib_class . ' not exists in addon ' . $id_addon . ' library!', E_USER_WARNING);
			return false;
		}

		// Set parameters
		$params['~config'] = $this->load_config($id_addon);
		$params['~log'] = $this->load_log($id_addon);
		$params['~id'] = $id_addon;

		/* Load new addon object into loaded addons array */
		self::$loaded_addons[$id_addon] = new $addon_lib_class($params);

		// Unset temporary values
		unset($params['~config']);
		unset($params['~log']);
		unset($params['~id']);

		tk_e::log_debug('Loaded `'.$id_addon.'` with class `'.$addon_lib_class.'` from path `' . $addon_path . '`'.
			' with params `' . implode(', ', $params) . '`.', __CLASS__.'->'.__FUNCTION__);

		// unset temporary values
		unset($params);

		/* Return addon object */
		return self::$loaded_addons[$id_addon];

	} // end func load

	/**
	 * Return array of loaded addons (names only), if variable id_addon is null.
	 * Else, return status of addon as bool.
	 *
	 * @access public
	 * @param string $id_addon
	 * @return mixed array | bool
	 */
	public function loaded($id_addon = NULL) {

		/* Return array with names of loaded addons */
		if(is_null($id_addon)) {
			return array_keys(self::$loaded_addons);
		}

		/* Return status of addon */
		if(array_key_exists($id_addon, self::$loaded_addons)) {
			return true;
		} else {
			return false;
		}

	} // end of func loaded

	/**
	 * Return addon directory path
	 * If addon path exists in both, app and framework, than return app path.
	 * Return false, if addon not exists.
	 *
	 * @access public
	 * @param string $id_addon
	 * @return mixed
	 * @since 2.0.0
	 */
	public function exist($id_addon) {

		$app_addon_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'lib' . TK_DS . $id_addon . '.addon.php';
		$tk_addon_file = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'lib' . TK_DS . $id_addon . '.addon.php';

		// Addon exists in app path
		if(is_file($app_addon_file)) {
			return TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		}

		// Addon exists in framework path
		if(is_file($tk_addon_file)) {
			return TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		}

		// Addon not exists
		return false;

	} // end func exist

	/**
	 * Return list of addon names
	 *
	 * @access public
	 * @param bool $tk_only = false
	 * @return array
	 */
	public function all($tk_only = false) {

		// Get addons from framework directory
		$tk_addons = $this->lib->file->ls(TK_PATH . 'addons', 'd');

		// Return only addons from framework directory
		if($tk_only == true) {

			if(!empty($tk_addons)) {
				sort($tk_addons);
			}

			return $tk_addons;
		}

		// Get addons from application directory
		$app_addons = $this->lib->file->ls(TK_APP_PATH . 'addons', 'd');

		// Return all addons
		$addons = array_merge($tk_addons, $app_addons);

		if(!empty($addons)) {
			sort($addons);
		}

		return $addons;

	} // End func all

	/**
	 * Return view file path.
	 * Return application view file path if exists.
	 * Return false if view file not exist.
	 *
	 * @access public
	 * @param string $id_addon
	 * @param mixed $id_module = NULL
	 * @param string $view_file
	 * @return bool | string
	 */
	public function view_exists($id_addon, $id_module = NULL, $view_file) {

		if(trim($id_addon) == '') {
			trigger_error('Addon name is empty.', E_USER_WARNING);
		}

		if(trim($view_file) == '') {
			trigger_error('View name is empty.', E_USER_WARNING);
		}

		/* Define path for view */
		$app_path = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		$tk_path = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS;

		if(!is_null($id_module)) {
			$app_path .= 'modules' . TK_DS . $id_module . TK_DS;
			$tk_path .= 'modules' . TK_DS . $id_module . TK_DS;
		}

		$app_path .= 'views' . TK_DS;
		$tk_path .= 'views' . TK_DS;

		$app_view_file = $app_path . $view_file . '.view.php';
		$tk_view_file = $tk_path . $view_file . '.view.php';

		/* View file not exists in any of path. */
		if(!is_file($tk_view_file) and !is_file($app_view_file)) {
			return false;
		}

		/* View file exists in app */
		if(is_file($app_view_file)) {
			return $app_view_file;
		/* View file exists in framework */
		} else {
			return $tk_view_file;
		}

	} // End func view_exists

	/**
	 * Return Module path if module exists in framework or app.
	 * If File exists in both, the function will return path from app.
	 * Return false if module not exists.
	 *
	 * @access public
	 * @param string $id_addon
	 * @param string $id_module
	 * @return mixed
	 */
	public function module_exists($id_addon, $id_module) {

		if(trim($id_addon) == '') {
			trigger_error('Addon name is empty.', E_USER_WARNING);
		}

		if(trim($id_module) == '') {
			trigger_error('Module name is empty.', E_USER_WARNING);
		}

		/* Set module filename for application dir. */
		$app_mod_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon .
			TK_DS . 'modules' . TK_DS . $id_module.'.module.php';

		/* Set module filename for framework dir. */
		$tk_mod_file = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS .
			'modules' . TK_DS . $id_module.'.module.php';

		/* Module file not exists in any of path. */
		if(!is_file($tk_mod_file) and !is_file($app_mod_file)) {
			return false;
		}

		/* Module exists in app */
		if(is_file($app_mod_file)) {
			return $app_mod_file;
			/* Module exists in framework */
		} else {
			return $tk_mod_file;
		}

	} // end func module exists

	/**
	 * Return Model path if model exists in framework or app.
	 * If File exists in both, the function will return path from app.
	 * Return false if model not exists.
	 *
	 * @access public
	 * @param string $id_addon
	 * @param string $id_model
	 * @return mixed
	 */
	public function model_exists($id_addon, $id_model) {

		if(trim($id_addon) == '') {
			trigger_error('Addon name is empty.', E_USER_WARNING);
		}

		if(trim($id_model) == '') {
			trigger_error('Model name is empty.', E_USER_WARNING);
		}

		/* Set model filename for application dir. */
		$app_mod_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon .
			TK_DS . 'models' . TK_DS . $id_model.'.model.php';

		/* Set model filename for framework dir. */
		$tk_mod_file = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS .
			'models' . TK_DS . $id_model.'.model.php';

		/* Model file not exists in any of path. */
		if(!is_file($tk_mod_file) and !is_file($app_mod_file)) {
			return false;
		}

		/* Model exists in app */
		if(is_file($app_mod_file)) {
			return $app_mod_file;
			/* Model exists in framework */
		} else {
			return $tk_mod_file;
		}

	} // end func model exists

	/**
	 * Load and return view file object
	 *
	 * @access public
	 * @param string $id_addon
	 * @param mixed $id_module = NULL
	 * @param string $view
	 * @param mixed $vars = NULL
	 * @return mixed bool|object
	 */
	public function load_view($id_addon, $id_module = NULL, $view, $vars = NULL) {

		tk_e::log_debug('Loading view: id addon: ' . $id_addon . ' / id_module: ' . $id_module. ' / view file: ' .$view, 'LOAD VIEW');

		// Define view file path
		$view_file = $this->view_exists($id_addon, $id_module, $view);

		if(!$view_file) {

			if(!is_null($id_module)) {
				trigger_error('View file "' . $view . '" not exists for module "' . $id_module .
					'", in addon "' . $id_addon . '".', E_USER_ERROR);
			} else {
				trigger_error('View file "' . $view . '" not exists for addon "' . $id_addon .
					'".', E_USER_ERROR);
			}

			return false;
		}

		$params = array();

		/* Load objects as parameters */
		$params['~config'] = $this->load_config($id_addon);
		$params['~log'] = $this->load_log($id_addon);

		/* define view file id */
		$params['~id'] = basename($view);

		/* Define module id */
		if(!is_null($id_module)) {
			$params['~id_module'] = $id_module;
		} else {
			$params['~id_module'] = '';
		}

		/* Define addon id */
		$params['~id_addon'] = $id_addon;

		/* Define new instance of view class */
		$view_obj = new view($view_file, $params, $vars);

		if($params['~id_module'] != '') {
			tk_e::log_debug('Loaded view file "'.$view_file.'` for addon "'.$id_addon.'", module "'.$id_module.'"'.
				' with vars count ' . count($vars) . '.',	__CLASS__.'->'.__FUNCTION__);
		} else {
			tk_e::log_debug('Loaded view file "'.$view_file.'` for addon "'.$id_addon.'"'.
				' with vars count ' . count($vars) . '`.',	__CLASS__.'->'.__FUNCTION__);
		}

		// Unset temporary parameters.
		unset($params);

		/* Return view object */
		return $view_obj;

	} // End func load_view

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
	 * @param string $id_addon
	 * @param string $id_module
	 * @param mixed $params
	 * @param bool $clone
	 * @return object
	 * @since 4.0.0
	 */
	final public function load_module($id_addon, $id_module, $params = array(), $clone = false) {

		if(trim($id_addon) == '') {
			trigger_error('Called load_module with empty id_addon!', E_USER_ERROR);
			return false;
		}

		if(trim($id_module) == '') {
			trigger_error('Called load_module with empty id_module!', E_USER_ERROR);
			return false;
		}

		$module_index = $id_addon . '_' . basename($id_module);
		$module_base_name = basename($id_module);

		/* Return module object, if it is already loaded */
		if(array_key_exists($module_index, self::$loaded_modules) and $clone == false) {
			return self::$loaded_modules[$module_index];
		}

		/* Get module file */
		$module_file_path = $this->module_exists($id_addon, $id_module);

		if(count(explode('/', $id_module)) > 1) {
			$module_parent_dir = dirname($id_module);
		} else {
			$module_parent_dir = '';
		}

		if(!$module_file_path) {
			trigger_error('Module file `' . $id_module . '` not exists for addon `'.$id_addon.'`.', E_USER_ERROR);
			return false;
		}

		/* Set extended module filename for application dir. */
		$ext_module_file_path = TK_APP_PATH . 'addons' . TK_DS . $id_addon .
			TK_DS . 'modules' . TK_DS . $id_module.'.ext.module.php';

		/* Include module file returned by module_exists() method */
		require_once($module_file_path);
		$module_class = $module_index . '_module';

		/* Check, if extended module exists in app */
		if(is_file($ext_module_file_path)) {
			require_once($ext_module_file_path);
			$module_file_path = $ext_module_file_path;
			$module_class = $module_index . '_ext_module';
		}

		/* Return false, if module class not exists */
		if(!class_exists($module_class)) {
			trigger_error('Module class `' . $module_class .
				'` not exists in module `'.$module_file_path.'` for addon `'.$id_addon.'`.', E_USER_ERROR);
			return false;
		}

		/* Set parameters for module constructor */
		$params['~config'] = $this->load_config($id_addon);
		$params['~log'] = $this->load_log($id_addon);
		$params['~id'] = $module_base_name;
		$params['~id_addon'] = $id_addon;
		$params['~parent_dir'] = $module_parent_dir;

		/* Load new module object into loaded modules array */
		$module = new $module_class($params);

		// Unset temporary parameters.
		unset($params['~config']);
		unset($params['~log']);
		unset($params['~id']);
		unset($params['~id_addon']);
		unset($params['~parent_dir']);

		/* Module loaded as singleton and will be appended to loaded modules array */
		if($clone == false) {
			self::$loaded_modules[$module_index] = $module;
		}

		if(is_array($params)) {
			$params_ = implode(',', $params);
		} else {
			$params_ = (string)$params;
		}

		tk_e::log_debug('Loaded module: "'.$module_file_path.'" Class: "'.$module_class.'" with params - "' . $params_ .'"', get_class($this) . '->' . __FUNCTION__);

		/* return module object */
		return $module;

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
	 * @param string $id_addon
	 * @param string $id_model
	 * @param mixed $instance
	 * @param bool $clone
	 * @return object
	 * @since 4.0.0
	 */
	final public function load_model($id_addon, $id_model, $instance = NULL, $clone = false) {

		if(trim($id_addon) == '') {
			trigger_error('Called load_model with empty id_addon!', E_USER_ERROR);
			return false;
		}

		if(trim($id_model) == '') {
			trigger_error('Called load_model with empty id_model!', E_USER_ERROR);
			return false;
		}

		$model_index = $id_addon . '_' . basename($id_model);

		/* Return model object, if it is already loaded */
		if(array_key_exists($model_index, self::$loaded_models) and $clone == false) {
			return self::$loaded_models[$model_index];
		}

		/* Get Model file */
		$model_file_path = $this->model_exists($id_addon, $id_model);

		if(!$model_file_path) {
			trigger_error('Model file for `' . $id_model .
				'` not exists for addon `' . $id_addon . '`.', E_USER_ERROR);
			return false;
		}

		/* Set extended model filename for application dir. */
		$ext_model_file_path = TK_APP_PATH . 'addons' . TK_DS . $id_addon .
			TK_DS . 'models' . TK_DS . $id_model.'.ext.model.php';

		/* Include model file returned by model_exists() method */
		require_once($model_file_path);
		$model_class = $model_index . '_model';

		/* Check, if extended model exists in app */
		if(is_file($ext_model_file_path)) {
			require_once($ext_model_file_path);
			$model_file_path = $ext_model_file_path;
			$model_class = $model_index . '_ext_model';
		}

		/* Return false, if model class not exists */
		if(!class_exists($model_class)) {
			trigger_error('Model class `' . $model_class .
				'` not exists in model `'.$model_file_path.'` for addon `'.$id_addon.'`.', E_USER_ERROR);
			return false;
		}

		/* Set required values for model */
		$params['~instance'] = $instance;
		$params['~log'] = $this->load_log($id_addon);
		$params['~id'] = $id_model;
		$params['~id_addon'] = $id_addon;
		$params['~language_prefix'] = $this->app->language();

		/* Load new model object into loaded models array */
		$model = new $model_class($params);

		/* Model loaded as singleton and will be appended to loaded models array */
		if($clone == false) {
			self::$loaded_models[$model_index] = $model;
		}

		tk_e::log_debug('Loaded model: "'.$model_file_path.'" Class: "'.$model_class.'" with instance - "' . $instance .'"', get_class($this) . '->' . __FUNCTION__);

		/* Unset temporary parameters */
		unset($params);

		/* return model object */
		return $model;

	} // end func load_model

	/**
	 * Load addon configuration object
	 *
	 * @access public
	 * @param string $id_addon
	 * @return object
	 */
	protected function load_config($id_addon) {

		// Return, if object already loaded
		if(isset(self::$loaded_configs[$id_addon])) {
			return self::$loaded_configs[$id_addon];
		}

		// Define Addon required values.
		// Load config object
		$app_config_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'config' . TK_DS . 'config.ini';
		$tk_config_file = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'config' . TK_DS . 'config.ini';

		if(file_exists($app_config_file)) {
			$config_file = $app_config_file;
		} elseif(file_exists($tk_config_file)) {
			$config_file = $tk_config_file;
		} else {
			trigger_error('Configuration file not exists for addon "' . $id_addon . '".', E_USER_ERROR);
		}

		// Set object into loaded objects array.
		self::$loaded_configs[$id_addon] = $this->lib->ini->instance($config_file, NULL, false);

		// Return loaded configuration object
		return self::$loaded_configs[$id_addon];

	} // End func load_config

	/**
	 * Load Addon log object
	 *
	 * @access public
	 * @param string $id_addon
	 * @return object
	 */
	public function load_log($id_addon) {

		// Return, if already loaded
		if(isset(self::$loaded_logs[$id_addon])) {
			return self::$loaded_logs[$id_addon];
		}

		// Log file extension defined in application configuration file
		$log_ext = $this->app->config('log_file_extension', 'ERROR_HANDLING');

		// Set object into loaded objects array.
		self::$loaded_logs[$id_addon] = $this->lib->log->instance('addon_' . $id_addon . '.' . $log_ext);

		// Return log object
		return self::$loaded_logs[$id_addon];

	} // End func load_log

/* End of class addons */
}

/* End of file */
?>