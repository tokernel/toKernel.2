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
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    3.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
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
     * Module params
     *
     * @access protected
     * @var array
     */
    protected $params = array();

    /**
     * Parent directory of module
     *
     * @var string
     */
    protected $parent_dir = '';

    /**
     * Class Constructor
     *
     * @access public
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {

        // Load main objects
        $this->lib = lib::instance();
        $this->app = app::instance();
        $this->addons = addons::instance();

        // Define configuration object
        $this->config = $params['~config'];

        // Define Addon's log object
        $this->log = $params['~log'];

        // Define Module, Addon id
        $this->id = $params['~id'];
        $this->id_addon = $params['~id_addon'];

        // Define parent directory path.
        if($params['~parent_dir'] != '') {
            $this->parent_dir = $params['~parent_dir'] . TK_DS;
        }

        $this->language = $params['~language'];

        // Unset temporary value
        unset($params['~config']);
        unset($params['~log']);
        unset($params['~id']);
        unset($params['~id_addon']);
        unset($params['~parent_dir']);
        unset($params['~language']);

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
        $parent_addon = $this->id_addon;
        return $this->addons->$parent_addon;
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
        return $this->addons->load_module($this->id_addon, $id_module, $params, $clone);
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
        return $this->addons->load_model($this->id_addon, $id_model, $instance, $clone);
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

        $module_dir = $this->parent_dir . $this->id;
        return $this->addons->load_view($this->id_addon, $module_dir, $file, $vars);

    } // end func load_view

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
    final public function config($item, $section = NULL) {
        return $this->config->item_get($item, $section);
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
     * Get language value by expression
     * Return language prefix if item is null.
     *
     * NOTE: From toKernel version 2.0.0 Languages supported only by application.
     * Modules not supporting own language files.
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

    /* End of class module */
}

/* End of file */
?>