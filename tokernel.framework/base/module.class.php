<?php
/**
 * toKernel - Universal PHP Framework.
 * Base module class for modules.
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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    3.1.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 *
 * @todo       Review comments
 * @todo       Review/Refactor Debugging.
 * @todo       Review/Refactor errors/exceptions/trigger_error ...
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * class module
 *
 * @author David A. <tokernel@gmail.com>
 */
class module {

    /**
     * Status of module
     *
     * @access protected
     * @staticvar bool
     */
    protected static $initialized;

    /**
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access protected
     */
    protected $lib;

    /**
     * Application object for accessing
     * aplication functions in this class
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
     * This module id
     *
     * @access protected
     * @var string
     */
    protected $id;

    /**
     * Addon id
     *
     * @access protected
     * @var string
     */
    protected $id_addon;

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
     * Addon language
     *
     * @access protected
     * @var object
     */
    protected $language;
    
    /**
     * Module own Language file path if file exists
     *
     * @access protected
     * @var string
     */
	protected $language_file;
	
    /**
     * Module params
     *
     * @access protected
     * @var array
     */
    protected $params = array();

    /**
     * Path of module
     * This value can be used internally to load views and language files.
     *
     * @access private
     * @var string
     */
    private $path = '';
	
	/**
	 * Parent addon object
	 *
	 * @access private
	 * @var object
	 */
    private $parent;
        
    /**
     * Class Constructor
     *
     * @access public
     * @param array $params
     */
    public function __construct($params = array()) {

        // Load main objects
        $this->lib = lib::instance();
        $this->app = app::instance();
        $this->addons = addons::instance();
	    
        // Define Parent object
	    $this->parent = $params['~parent'];
        
        // Define configuration object
        $this->config = $params['~config'];

        // Define Addon's log object
        $this->log = $params['~log'];
	    
        // Define Language object
	    $this->language = $params['~language'];
	    $this->language_file = $params['~language_file'];
	    
	    /* Define string values */
	    $this->id = $params['~id'];
	    $this->id_addon = $params['~id_addon'];
	    $this->path = $params['~path'];
	    
        // Unset temporary value
	    unset($params['~parent']);
        unset($params['~config']);
        unset($params['~log']);
        unset($params['~language']);
        unset($params['~language_file']);
	    unset($params['~id']);
	    unset($params['~id_addon']);
	    unset($params['~path']);
        
        // Set module params
        $this->params = $params;

        // Initialized
        self::$initialized = true;

    } // End constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        unset($this->config);
        unset($this->log);
        unset($this->id);
        unset($this->id_addon);
        unset($this->parent_dir);
        unset($this->params);
        unset($this->language);

    } // end destructor
	
    /**
     * Return parent directory of module
     *
     * @access protected
     * @return mixed
     */
    protected function get_parent_dir() {
        $rc = new ReflectionClass(get_class($this));
        return basename(dirname($rc->getFileName()));
    }

    /**
     * Return parent addon object of module
     *
     * @access public
     * @return mixed
     */
    public function addon() {
    	return $this->parent;
    }

    /**
     * Load module by parent addon object
     *
     * @final
     * @access public
     * @param string $id_module
     * @param array $params
     * @param bool $clone
     * @return object loaded module
     */
    final public function load_module($id_module, $params = array(), $clone = false) {
    	return $this->parent->load_module($id_module, $params, $clone);
    }

    /**
     * Load model by parent addon object
     *
     * @final
     * @access public
     * @param string $id_model
     * @param mixed $instance
     * @param bool $clone
     * @return object loaded model
     */
    final public function load_model($id_model, $instance = NULL, $clone = false) {
		return $this->parent->load_model($id_model, $instance, $clone);
    }

    /**
     * Load view file for module and return `view` object.
     * Include view file from application dir if exists,
     * else include from framework dir.
     * Return false, if view file not exists.
     *
     * @final
     * @access public
     * @param string $file
     * @param array $vars = array()
     * @return mixed string | false
     * @since 2.1.0
     */
    final public function load_view($file, $vars = array()) {
	
	    tk_e::log_debug('Loading view: Addon: ' . $this->id_addon . ' / Module: '.$this->id.' / view file: ' .$file, 'LOAD VIEW');
	
	    // Define view file path
	    $view_file = $this->view_exists($file);
	
	    if(!$view_file) {
		
		    trigger_error('View file "' . $file . '" not exists for addon "' . $this->id_addon . ' / Module: '.$this->id.' ".', E_USER_ERROR);
		    return false;
	    }
	
	    $params = array();
	
	    /* Load objects as parameters */
	    $params['~config'] = & $this->config;
	    $params['~log'] = & $this->log;
	    $params['~language'] = & $this->language;
		    
	    /* define view file string values */
	    $params['~id'] = basename($file);
	    $params['~id_addon'] = $this->id_addon;
	    $params['~id_module'] = $this->id;
	    $params['~language_file'] = $this->language_file;
	    
	    /* Define new instance of view class */
	    $view_obj = new view($view_file, $params, $vars);
	
	    tk_e::log_debug('Loaded view file "'.$file.'` for addon "'.$this->id_addon.' / Module: '.$this->id.'"'. ' with vars count ' . count($vars) . '`.', __CLASS__.'->'.__FUNCTION__);
	
	    // Unset temporary parameters.
	    unset($params);
	
	    /* Return view object */
	    return $view_obj;
	    
    } // end func load_view
	
	public function view_exists($view_file) {
		
		if(trim($view_file) == '') {
			trigger_error('View name is empty.', E_USER_WARNING);
		}
		
		$view_file = str_replace('/', TK_DS, $view_file);
		
		/* Define path for view */
		$view_file_path = $this->path . $this->id . TK_DS . 'views' . TK_DS . $view_file . '.view.php';
		
		/* View file exists in application. */
		if(is_file($view_file_path)) {
			return $view_file_path;
		}
		
		/* View file not exists */
		return false;
		
	} // End func view_exists

    /**
     * Return true if addon called from backend url or
     * backend_dir is empty (not set) in configuration.
     * Else, redirect to error_404
     *
     * @access public
     * @return bool
     * @since 2.3.0
     */
    public function check_backend() {

        if($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
            $this->app->error_404('Cannot call method of class `' . get_class($this) . '` by this url.');
            return false;
        }

        return true;

    } // End func check_backend

    /**
     * Return addon configuration values
     *
     * @final
     * @access public
     * @param string $item
     * @param string $section
     * @return mixed
     */
    final public function config($item = NULL, $section = NULL) {
    	return $this->parent->config($item, $section);
    } // end func config

    /**
     * Return addon id of this module
     *
     * @access public
     * @return string
     */
    public function id_addon() {
        return $this->id_addon;
    }
	
	/**
	 * Return language value by expression
	 * NOTICE: Not all modules required to have own language object.
	 * If language file exists in module directory,
	 * the language object wil be loaded automatically.
	 *
	 * Try to return from addon language
	 * if item not exists in own language or language object not loaded.
	 *
	 * Try to return from application language
	 * if item not exists in own language object.
	 *
	 * @access public
	 * @param string $item
	 * @param array $lng_args = array()
	 * @return string
	 */
	public function language($item, array $lng_args = array()) {
		
		$value = $this->language->get($item, $lng_args);
				
		/* Item exists in language object */
		if($value) {
			return $value;
		}
		
		/* Module file has own language file loaded and the item not exists.
		   Now trying to get language value from own addon */
		if($this->language_file != '') {
			return $this->parent->language($item, $lng_args);
		}
				
		/* Finally try ot get language value from application language */
		return $this->app->language($item, $lng_args);
				
	} // end func language

    /**
     * Return module param by key
     *
     * @access public
     * @param string $key
     * @return mixed
     * @since  3.0.0
     */
    public function param($key) {

        if(!isset($this->params[$key])) {
            return false;
        }

        return $this->params[$key];

    } // end func param

} /* End of class module */