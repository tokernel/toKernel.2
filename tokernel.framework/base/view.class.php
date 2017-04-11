<?php
/**
 * toKernel - Universal PHP Framework.
 * Base View class for addons and modules.
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
 * @version    2.1.2
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
 * class view
 *
 * @author David A. <tokernel@gmail.com>
 */
class view {

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
	 * and this is the module's view.
	 *
	 * @access protected
	 * @var string
	 */
	protected $language_file;

    /**
     * Buffer (html content)
     *
     * @access private
     * @var string
     */
    protected $_buffer = NULL;

    /**
     * Mixed variables
     *
     * @access protected
     * @var array
     */
    protected $variables = array();

    /**
     * View file with full path
     *
     * @access protected
     * @var string
     */
    protected $file = '';

    /**
     * View file ID
     *
     * Access protected
     * @var string
     */
    protected $id;

    /**
     * View's owner addon id
     *
     * @access protected
     * @var string
     */
    protected $id_addon;

    /**
     * View file module id
     *
     * @access protected
     * @var string
     */
    protected $id_module;


    /**
     * Class constructor
     *
     * @access public
     * @param string $file
     * @param array $params
     * @param array $vars
     * @return void
     */
    public function __construct($file, $params, $vars) {

        // Define main objects
        $this->lib = lib::instance();
        $this->app = app::instance();
        $this->addons = addons::instance();

        // Define View file to include
        $this->file = $file;

        // Define addon configuration object
        $this->config = $params['~config'];

        // Define addon log object
        $this->log = $params['~log'];

        // Define View, Addon, Module id
        $this->id = $params['~id'];
        $this->id_addon = $params['~id_addon'];
        $this->id_module = $params['~id_module'];
        $this->language = $params['~language'];
        
        /* This assumes if the file path is not empty,
           this view file loaded by module and language file exists for module */
        $this->language_file = $params['~language_file'];

        // Unset temporary params
        unset($params);

        // Set view file variables
        $this->variables = $vars;

    } // end constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        unset($this->file);
        unset($this->config);
        unset($this->log);
        unset($this->id);
        unset($this->id_addon);
        unset($this->id_module);
        unset($this->variables);
        unset($this->language);
        unset($this->_buffer);

    } // end destructor

    /**
     * Return variable by name
     *
     * @access public
     * @param string $var
     * @return mixed
     */
    public function __get($var) {
        
    	if(isset($this->variables[$var])) {
            return $this->variables[$var];
        }
        	
        $message = 'Undefined variable `'.$var.'` in view object. ';
        $message .= 'Addon: ' . $this->id_addon;
        	
        if($this->id_module != '') {
        	$message .= ' Module: ' . $this->id_module;
	    }
        	
		trigger_error($message, E_USER_NOTICE);
        	
        return NULL;
                
    } // end func __get

    /**
     * Set variable by name
     *
     * @access public
     * @param string $var_name
     * @param mixed $var_value
     * @return void
     */
    public function __set($var_name, $var_value) {
        $this->variables[$var_name] = $var_value;
    }

    /**
     * Unset a variable by name
     *
     * @access public
     * @param string $var
     * @return mixed
     * @since 1.3.0
     */
    public function __unset($var) {

        if(isset($this->variables[$var])) {
            unset($this->variables[$var]);
        }

    } // end func __unset

    /**
     * Check whether a variable has been defined
     *
     * @access public
     * @param string $var
     * @return mixed
     * @since 1.3.0
     */
    public function __isset($var) {

        if(isset($this->variables[$var])) {
            return true;
        } else {
            return false;
        }

    } // end func __isset

    /**
     * Reset all variables
     *
     * @access public
     * @return void
     * @since 1.2.0
     */
    public function reset() {
        $this->variables = array();
        $this->_buffer = '';
    }

    /**
     * Return id_addon of this view
     *
     * @access public
     * @return string
     */
    public function id_addon() {
        return $this->id_addon;
    }

    /**
     * Return View file owner addon object
     *
     * @access public
     * @return mixed
     */
    public function addon() {
        $parent_addon = $this->id_addon;
        return $this->addons->$parent_addon;
    }

    /**
     * Set variable
     *
     * @access public
     * @param string | array $var_name
     * @param string $value = NULL
     * @return void
     */
    public function set_var($var_name, $value = NULL) {

        if(is_array($var_name)) {
            foreach($var_name as $n => $v) {
                $this->variables[$n] = $v;
            }
        } else {
            $this->variables[$var_name] = $value;
        }

    } // end func set_var

    /**
     * Get variable by name
     *
     * @access public
     * @param string $var_name
     * @return mixed
     */
    public function get_var($var_name) {

        if(isset($this->variables[$var_name])) {
            return $this->variables[$var_name];
        } else {
            return NULL;
        }

    } // end func get_var

    /**
     * Call Interpreter and echo the content.
     *
     * @access public
     * @param array | null $variables
     * @return void
     * @since version 2.1.2
     */
    public function show($variables = array()) {
        echo $this->run($variables);
    }

    /**
     * Interpret view file and return buffer
     *
     * @access public
     * @param array $variables
     * @return string
     */
    public function run($variables = array()) {

        /* Merge all variables to parse */
        $this->variables = array_merge(
            $this->app->get_vars(),
            $this->variables,
            $variables
        );
	    
        ob_start();

        require($this->file);

        $this->_buffer .= ob_get_contents();
	    
        ob_end_clean();

        /* Replace all variables */
        if(!empty($this->variables)) {
            foreach($this->variables as $var => $value) {
                // Convert only scalar.
                if(is_scalar($value)) {
                    $this->_buffer = str_replace('{var.'.$var.'}', $value, $this->_buffer);
                }
            }
        }

        tk_e::log_debug('End parsing view file: "' .
            basename($this->file) . '". In Addon: "' . $this->id . '"',
            get_class($this) . '->' . __FUNCTION__);

        return $this->_buffer;

    } // End func run
	
	/**
	 * Return language value by expression
	 * NOTICE: Not all modules required to have own language object.
	 * If language file exists in module directory,
	 * the language object wil be loaded automatically.
	 *
	 * Try to load from Application
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
		
		/* Try to get value from own language object
		   The language object can be owned by addon or module depends on module language file exists */
		$value = $this->language->get($item, $lng_args);
		
		/* Item exists in own language object */
		if($value) {
			return $value;
		}
		
		/* Check if this is view file of module and the language file owned by module,
		   than try to get language value from addon */
		if($this->language_file != '') {
			$id_addon = $this->id_addon;
			return $this->addons->$id_addon->language($item, $lng_args);
		}
		
		/* Finally try ot get language expression from application language */
		return $this->app->language($item, $lng_args);
		
	} // end func language
	
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
		$id_addon = $this->id_addon;
		return $this->addons->$id_addon->config($item, $section);
	} // end func config

} /* End of class view */