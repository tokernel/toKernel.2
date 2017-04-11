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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    6.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo       Review/Refactor Debugging.
 * @todo       Review/Refactor errors/exceptions/trigger_error ...
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
     * Main Addons objectto access all addons.
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
     * Addon log object
     *
     * @var object
     * @access protected
     */
    protected $log;

    /**
     * Addon language object
     *
     * @access protected
     * @var object
     */
    protected $language;
	
	/**
	 * Parameters set by constructor
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $params;
	
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
	 * Current language prefix
	 *
	 * @access protected
	 * @staticvar string
	 */
	private static $language_prefix;
	
    /**
     * Class constructor
     *
     * @access public
     * @param array $params
     */
    public function __construct($params = array()) {

        /* Set main objects */
	    $this->app = app::instance();
	    $this->lib = lib::instance();
        $this->addons = addons::instance();
	    
        /* Define id_addon */
        $id_addon = get_class($this);
        $id_addon = substr($id_addon, 0, -6);
        
        $this->id = $id_addon;
        
        /* Load config object */
	    $config_file = TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'config' . TK_DS . 'config.ini';
	    $this->config = $this->lib->ini->instance($config_file, NULL, false);
        
	    /* Load log object
	       Log file extension defined in application configuration file */
	    $log_ext = $this->app->config('log_file_extension', 'ERROR_HANDLING');
	    $this->log = $this->lib->log->instance('addon_' . $id_addon . '.' . $log_ext);
	    
	    /* Define language prefix */
	    if(TK_RUN_MODE == 'cli') {
		    self::$language_prefix = $this->lib->cli->language_prefix();
	    } else {
		    self::$language_prefix = $this->lib->url->language_prefix();
	    }
	    
        /* Load language object */
	    $this->language = $this->lib->language->instance(TK_APP_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 'languages' . TK_DS . self::$language_prefix . '.ini');
        
        /* Unset temporary variables. */
	    unset($config_file);
	    unset($log_ext);
	    unset($language_prefix);
	    unset($id_addon);
	    
        /* Set parameters */
        $this->params = $params;

    } // end constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        unset($this->id);
    	unset($this->config);
        unset($this->log);
        unset($this->language);
	    unset($this->params);
    } // end destructor
	
	/**
	 * Return Module file path if exists.
	 * Return false if not exists.
	 *
	 * @access public
	 * @param string $id_module
	 * @return mixed
	 * @since Version 6.0.0
	 */
	public function module_exists($id_module) {
		
		/* Check if module id is empty */
		if(trim($id_module) == '') {
			trigger_error('Module name is empty! Called in addon `'.$this->id.'`.', E_USER_WARNING);
			return false;
		}
		
		/* Replace directory separator */
		$id_module = str_replace('/', TK_DS, $id_module);
		
		/* Define module file name. */
		$module_file = TK_APP_PATH . 'addons' . TK_DS . $this->id . TK_DS . 'modules' . TK_DS . $id_module.'.module.php';
		
		/* Module file exists. */
		if(is_file($module_file)) {
			return $module_file;
		}
		
		return false;
		
	} // end func module exists
	
    /**
     * Load Module by id and store into local loaded modules array.
     * Return Module object if successfully loaded.
     * Return false on failure.
     *
     * It is possible to load new instance of module object by setting the last argument as true.
     *
     * @final
     * @access public
     * @param string $id_module
     * @param mixed $params
     * @param bool $clone
     * @return mixed object | bool
     * @since 4.0.0
     */
    final public function load_module($id_module, $params = array(), $clone = false) {
	    
    	/* Check if module id is empty */
	    if(trim($id_module) == '') {
		    trigger_error('Called load_module with empty id_module in addon `'.$this->id.'`!', E_USER_ERROR);
		    return false;
	    }
	    
	    /* Define module index
	       The template is: {id_addon}_{id_module} */
	    $module_index = $this->id . '_' . basename($id_module);
	    	
	    /* Return module object, if it is already loaded
	       and no cloning required */
	    if(array_key_exists($module_index, self::$loaded_modules) and $clone == false) {
		    return self::$loaded_modules[$module_index];
	    }
	
	    /* Get module file path */
	    $module_file_path = $this->module_exists($id_module);
	    
	    /* Check if module file exists and returned */
	    if(!$module_file_path) {
		    trigger_error('Module file `' . $id_module . '` not exists for addon `'.$this->id.'`.', E_USER_ERROR);
		    return false;
	    }
		
	    /* Include module file returned by module_exists() method */
	    require_once($module_file_path);
	    
	    /* Define module class name.
	       The template is: {id_addon}_{id_module}_module */
	    $module_class = $module_index . '_module';
	
	    /* Return false, if module class not exists */
	    if(!class_exists($module_class)) {
		    trigger_error('Module class `'.$module_class.'` not exists in module `'.$module_file_path.'` for addon `'.$this->id.'`.', E_USER_ERROR);
		    return false;
	    }
	
	    /* Set string params */
	    $params['~id'] = basename($id_module);
	    $params['~id_addon'] = $this->id;
	    $params['~path'] = dirname($module_file_path) . TK_DS;
	    
	    /* Set parameters for module constructor by reference */
	    $params['~parent'] = & $this;
	    $params['~config'] = & $this->config;
	    $params['~log'] = & $this->log;
			    
	    /* Define module own language file */
	    $language_file = dirname($module_file_path) . TK_DS . basename($id_module) . TK_DS . 'languages' . TK_DS . self::$language_prefix . '.ini';
	    
	    /* Load language object if language file exists for module */
	    if(is_file($language_file)) {
	    	
		    $params['~language'] = $this->lib->language->instance($language_file);
		    $params['~language_file'] = $language_file;
	    /* Set own (addon's) language object */
	    } else {
		    $params['~language'] = & $this->language;
		    $params['~language_file'] = '';
	    }
	    	    	    
	    /* Define new module object */
	    $module = new $module_class($params);
	
	    /* Unset temporary parameters. */
	    unset($params['~id']);
	    unset($params['~id_addon']);
	    unset($params['~path']);
	    
	    unset($params['~parent']);
	    unset($params['~config']);
	    unset($params['~log']);
	    unset($params['~language']);
	    	
	    /* Add module object into loaded modules array if no cloning required. */
	    if($clone == false) {
		    self::$loaded_modules[$module_index] = $module;
	    }
	    
	    /* Prepare parameters to debug */
	    if(is_array($params)) {
		    $params_ = implode(',', $params);
	    } else {
		    $params_ = (string)$params;
	    }
	    
	    tk_e::log_debug('Loaded module: "'.$module_file_path.'" Class: "'.$module_class.'" with params - "' . $params_ .' in addon: `'.$this->id.'`."', get_class($this) . '->' . __FUNCTION__);
	
	    /* return module object */
	    return $module;

    } // end func load_module

    /**
     * Load view file if exists and return object.
     *
     * @final
     * @access public
     * @param string $file
     * @param array $vars
     * @return mixed object | bool
     * @since 2.0.0
     */
    final public function load_view($file, $vars = array()) {
	
	    tk_e::log_debug('Loading view: id addon: ' . $this->id . ' / view file: ' .$file, 'LOAD VIEW');
	
	    /* Define view file path */
	    $view_file = $this->view_exists($file);
	
	    /* Check if view file exists */
	    if(!$view_file) {
		    trigger_error('View file "' . $file . '" not exists for addon "' . $this->id . '".', E_USER_ERROR);
		    return false;
	    }
	
	    $params = array();
	
	    /* Define view required objects by reference */
	    $params['~config'] = & $this->config;
	    $params['~log'] = & $this->log;
	    $params['~language'] = & $this->language;
	    
	    /* Define view file ids */
	    $params['~id'] = basename($file);
		$params['~id_addon'] = $this->id;
	    $params['~id_module'] = '';
	    $params['~language_file'] = '';

	    /* Define new instance of view class */
	    $view_obj = new view($view_file, $params, $vars);
	
	    tk_e::log_debug('Loaded view file "'.$file.'` for addon "'.$this->id.'"'. ' with vars count ' . count($vars) . '`.', __CLASS__.'->'.__FUNCTION__);
	    	
	    /* Unset temporary parameters */
	    unset($params);
	
	    /* Return view object */
	    return $view_obj;
	
    } // end func load_view
	
	/**
	 * Return View file path if exists.
	 * Return false if not exists.
	 *
	 * @access public
	 * @param string $view_file
	 * @return mixed
	 * @since Version 6.0.0
	 */
	public function view_exists($view_file) {
		
		/* Check if view file name is empty */
		if(trim($view_file) == '') {
			trigger_error('View name is empty. Called in addon `'.$this->id.'` ', E_USER_WARNING);
		}
		
		/* Replace directory separators */
		$view_file = str_replace('/', TK_DS, $view_file);
		
		/* Define view file path */
		$view_file_path = TK_APP_PATH . 'addons' . TK_DS . $this->id . TK_DS . 'views' . TK_DS . $view_file . '.view.php';
		
		/* View file exists */
		if(is_file($view_file_path)) {
			return $view_file_path;
		}
		
		/* View file not exists */
		return false;
		
	} // End func view_exists
	
	/**
	 * Load Model by id and store into local loaded models array.
	 * Return Model object if successfully loaded.
	 * Return false on failure.
	 *
	 * It is possible to load new instance of model object by setting the last argument as true.
	 *
	 * @final
	 * @access public
	 * @param string $id_model
	 * @param string $instance
	 * @param bool $clone
	 * @return mixed object | bool
	 * @since 6.0.0
	 */
	final public function load_model($id_model, $instance = NULL, $clone = false) {
		
		/* Check if model id is empty */
		if(trim($id_model) == '') {
			trigger_error('Called load_model with empty id_model in addon `'.$this->id.'`!', E_USER_ERROR);
			return false;
		}
		
		/* Define model index
	       The template is: {id_addon}_{id_model} */
		$model_index = $this->id . '_' . basename($id_model);
		
		/* Return model object, if it is already loaded and no cloning required */
		if(array_key_exists($model_index, self::$loaded_models) and $clone == false) {
			return self::$loaded_models[$model_index];
		}
		
		/* Get Model file */
		$model_file_path = $this->model_exists($id_model);
		
		/* Check if model file exists */
		if(!$model_file_path) {
			trigger_error('Model file for `' . $id_model .'` not exists for addon `' . $this->id . '`.', E_USER_ERROR);
			return false;
		}
		
		/* Include model file returned by model_exists() method */
		require_once($model_file_path);
		
		/* Define model class name.
	       The template is: {id_addon}_{id_model}_model */
		$model_class = $model_index . '_model';
		
		/* Return false, if model class not exists */
		if(!class_exists($model_class)) {
			trigger_error('Model class `' . $model_class .
				'` not exists in model `'.$model_file_path.'` for addon `'.$this->id.'`.', E_USER_ERROR);
			return false;
		}
		
		/* Set required values for model */
		// '~instance' is the section name in configuration file.
		$params['~instance'] = $instance;
		$params['~id'] = $id_model;
		$params['~id_addon'] = $this->id;
		$params['~language_prefix'] = self::$language_prefix;
		
		/* Set objects by reference */
		$params['~log'] = & $this->log;
				
		/* Load new model object */
		$model = new $model_class($params);
		
		/* If no cloning required, add model object to local loaded models array. */
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
	 * Return model file path if exists.
	 * Return false if not exists.
	 *
	 * @access public
	 * @param string $id_model
	 * @return mixed
	 * @since Version 6.0.0
	 */
	public function model_exists($id_model) {
		
		/* Check if model id is empty */
		if(trim($id_model) == '') {
			trigger_error('Model name is empty. Addon: `'.$this->id.'`', E_USER_WARNING);
			return false;
		}
		
		/* Replace directory separators */
		$id_model = str_replace('/', TK_DS, $id_model);
		
		/* Set model filename */
		$model_file = TK_APP_PATH . 'addons' . TK_DS . $this->id . TK_DS . 'models' . TK_DS . $id_model.'.model.php';
		
		/* Model file exists. */
		if(is_file($model_file)) {
			return $model_file;
		}
		
		return false;
	
    } // end func model_exists

    /**
     * Return addon configuration values.
     * Return section items as array if item is null and section defined,
     * else, return value by item
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
     * Return language value by expression
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
	    
    	/* Try to get value from own language object */
    	$value = $this->language->get($item, $lng_args);
    	
    	/* Item exists in language object */
    	if($value) {
    		return $value;
	    }
    	
    	/* Value not exists, try to get from application language */
	    return $this->app->language($item, $lng_args);
	    
    } // end func language

    /**
     * Return addon url
     * Example: http://localhost/my_project/application/addons/my_addon
     *
     * @access public
     * @return string
     * @since 5.0.0
     */
    public function url() {
        return $this->lib->url->base_url() . '/' . TK_APP_DIR . '/addons/' . $this->id . '/';
    }

    /**
     * Return addon path
     * Example: /var/www/my_project/application/addons/my_addon/
     *
     * @access public
     * @return string
     * @since 5.0.0
     */
    public function path() {
        return TK_ROOT_PATH . TK_APP_DIR . TK_DS . 'addons' . TK_DS . $this->id . TK_DS;
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
	    
    	if(!$this->is_backend()) {
		    $this->app->error_404('This action of addon `'.get_class($this).'` allowed to access only from backend!');
	    }

        return true;
    }

    /**
     * Except to define action_() method in child classes.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function action_() {}

    /**
     * Except to define action_ax_() method in child classes.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function action_ax_() {}

    /**
     * Except to define cli_() method in child classes.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function cli_() {}

} /* End of class addon */