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
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
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
        } else {
            return NULL;
        }
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
        $this->_buffer = NULL;
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
            return false;
        }

    } // end func get_var

    /**
     * Call Interpreter and echo the content.
     *
     * @access public
     * @return void
     */
    public function show() {
        echo $this->run();
    }

    /**
     * Interpret view file and return buffer
     *
     * @access public
     * @param array $variables
     * @return string
     */
    public function run($variables = array()) {

        ob_start();

        require($this->file);

        $this->_buffer .= ob_get_contents();
        ob_end_clean();

        /* Merge all variables to parse */
        $vars = array_merge(
            $this->app->get_vars(),
            $this->variables,
            $variables
        );

        /* Replace all variables */
        if(!empty($vars)) {
            foreach($vars as $var => $value) {
                // Convert only string or integer values.
                if(is_string($value) or is_numeric($value)) {
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
     * Get language value by expression
     * Return language prefix if item is null.
     *
     * NOTE: From toKernel version 2.0.0 Languages supported only by application.
     * Addons and Modules not supporting own language files.
     *
     * @final
     * @access public
     * @param string $item
     * @return string
     */
    final public function language($item = NULL) {

        if(is_null($item)) {
            return $this->app->language();
        }

        if(func_num_args() > 1) {
            $l_args = func_get_args();

            unset($l_args[0]);

            if(is_array($l_args[1])) {
                $l_args = $l_args[1];
            }

            return $this->language->get($item, $l_args);
        }

        return $this->language->get($item);

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
        return $this->config->item_get($item, $section);
    } // end func config

    /* End of class view */
}

/* End of file */
?>