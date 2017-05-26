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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 * @todo       Review language functionality
 * @todo       Review/Refactor Debugging.
 * @todo       Review/Refactor errors/exceptions/trigger_error ...
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
	 * Object single instance
	 *
	 * @staticvar object
	 * @access private
	 */
	private static $instance;

	/**
	 * Main Application object to access
	 * application provided functionality.
	 *
	 * @var object
	 * @access protected
	 */
	protected $app;

	/**
	 * Library object to access all libraries.
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
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
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
		trigger_error( 'Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR);
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
	 * Addons getter class
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
	 * Load Adddon by id and store into local loaded addons array.
	 * Return Addon object if successfully loaded.
	 * Return false on failure.
	 *
	 * @access public
	 * @param string $id_addon
	 * @param array $params
	 * @return mixed object | bool
	 */
	public function load($id_addon, $params = array()) {
		
		/* Addon id cannot be empty. */
		if(trim($id_addon) == '') {
			trigger_error('Called addons->load() with empty id_addon!', E_USER_ERROR);
			return false;
		}

		/* Return addon object, if it is already loaded */
		if(array_key_exists($id_addon, self::$loaded_addons)) {
			return self::$loaded_addons[$id_addon];
		}
		
		/* Get addon path */
		$addon_path = $this->exist($id_addon);

		/* Check if addon not exist */
		if(!$addon_path) {
			trigger_error('Addon `' . $id_addon . '` not exists.', E_USER_ERROR);
		}

		/* Define addon file name */
		$addon_lib_file = $addon_path . 'lib' . TK_DS . $id_addon . '.addon.php';

        /* Include addon file */
        require_once $addon_lib_file;

		/* Define addon class name */
		$addon_lib_class = $id_addon . '_addon';

		/* Check id addon class exist */
		if(!class_exists($addon_lib_class)) {
			trigger_error('Class ' . $addon_lib_class . ' not exists in addon ' . $id_addon . ' library!', E_USER_WARNING);
			return false;
		}
		
		/* Load new addon object into loaded addons array */
		self::$loaded_addons[$id_addon] = new $addon_lib_class($params);

		tk_e::log_debug('Loaded `'.$id_addon.'` with class `'.$addon_lib_class.'` from path `' . $addon_path . '`'.
			' with params `' . implode(', ', $params) . '`.', __CLASS__.'->'.__FUNCTION__);

		/* unset temporary values */
		unset($params);

		/* Return loaded addon object */
		return self::$loaded_addons[$id_addon];

	} // end func load

	/**
	 * If the argument is null, return all loaded addons list as array.
	 * Return addon loaded status as boolean if argument $addon_id is not null.
	 *
	 * @access public
	 * @param string $id_addon
	 * @return mixed array | bool
	 */
	public function loaded($id_addon = NULL) {

		/* Return array with loaded addon ids */
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
	 * Return list of all addon ids
	 * This is directories list in /application/addons/
	 *
	 * Notice: Even if an addon library file not exists, directory will be counted.
	 *
	 * @access public
	 * @return array
     * @since  2.0.0
	 */
	public function all() {

		/* Get addons from application directory */
		$addons = $this->lib->file->ls(TK_APP_PATH . 'addons', 'd');
		
		/* Sort addons */
		if(!empty($addons)) {
			sort($addons);
		}

		return $addons;

	} // End func all
	
	/**
	 * Return addon directory path if exists.
	 * If addon not exists false will be returned.
	 *
	 * @access public
	 * @param  string $id_addon
	 * @return mixed
	 * @since  2.0.0
	 */
	public function exist($id_addon) {
		
		/* Define addon library file path */
		$app_addon_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'lib' . TK_DS . $id_addon . '.addon.php';
		
		/* Addon exists */
		if(is_file($app_addon_file)) {
			return TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		}
		
		/* Addon not exists */
		return false;
		
	} // end func exist

} /* End of class addons */