<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for parsing and working with URL
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    4.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * url_lib class library
 *
 * @author David A. <tokernel@gmail.com>
 */
class url_lib {

    /**
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access protected
     */
    protected $lib;

    /**
     * Status of initialization
     *
     * @access protected
     * @staticvar bool
     */
    protected static $initialized;

    /**
     * Default language prefix
     *
     * @access protected
     * @var string
     */
    protected $language_prefix = 'en';

    /**
     * Allowed languages by mode frontend | backend
     *
     * @access protected
     * @var array
     */
    protected $allowed_languages = array('en');

    /**
     * LParse language from request URL
     *
     * @access protected
     * @var bool
     */
    protected $language_parsing = false;

    /**
     * Mode frontend | backend
     *
     * @access protected
     * @var string
     */
    protected $mode = TK_FRONTEND;

    /**
     * Backend dir
     *
     * @access protected
     * @var string
     */
    protected $backend_dir = '';

    /**
     * Parsed parameters
     *
     * @access protected
     * @var array
     */
    protected $params = array();

    /**
     * Parsed parts
     *
     * @access protected
     * @var array
     */
    protected $parts = array();

    /**
     * Loaded addon id
     *
     * @access protected
     * @var string
     */
    protected $addon = '';

    /**
     * Loaded addons's action
     *
     * @access protected
     * @var string
     */
    protected $action = '';

    /**
     * Base URL
     *
     * @access protected
     * @var string
     */
    protected $base_url = '';

    /**
     * Query string
     *
     * @access protected
     * @var string
     */
    protected $query_string = '';

    /**
     * Class constructor
     *
     * @access public
     */
    public function  __construct() {

        $this->lib = lib::instance();

        self::$initialized = false;

        $this->base_url = $this->dynamic_url();

    } // end constructor

    /**
     * Initialize URL Processing
     *
     * @access public
     * @param array $config
     * @return bool
     */
    public function init($config) {

        /* Return true if already initialized */
        if(self::$initialized == true) {
            trigger_error('Library "' . __CLASS__ . '" is already initialized!', E_USER_WARNING);
            return false;
        }

        // Set base URL if defined in configuration
        if($config->item_get('base_url', 'HTTP') != '') {
            $this->base_url = $config->item_get('base_url', 'HTTP');
            $this->base_url = rtrim($this->base_url, '/');
        }

        if(!$this->lib->filter->server('QUERY_STRING')) {

            // Define language prefix by default.
            $this->define_language_prefix($config, TK_FRONTEND);

            $this->mode = TK_FRONTEND;
            $this->backend_dir = '';
            $this->addon = $config->item_get('frontend_default_callable_addon', 'HTTP');
            $this->action = $config->item_get('frontend_default_callable_action', 'HTTP');
            $this->params = array();
            $this->parts = array();

            self::$initialized = true;
            return true;

        }

        $query_string = str_replace($config->item_get('http_get_var', 'HTTP') . '=',	'',	$this->lib->filter->server('QUERY_STRING'));
        $query_string = trim($query_string, '/');

        $this->query_string = $query_string;

        $params = explode('/', $query_string);

        $this->parts = $params;

        // Define mode frontend | backend
        // $params[0] can be language prefix and the $params[1] can be backend dir name
        // Case 1 backend: /{backend_dir}/...
        if(isset($params[0]) and $params[0] == $config->item_get('backend_dir', 'HTTP')) {
            $mode = TK_BACKEND;
            $backend_dir = $config->item_get('backend_dir', 'HTTP');
        // Case 2 backend: /{language_prefix}/{backend_dir}/...
        } elseif(isset($params[1]) and $params[1] == $config->item_get('backend_dir', 'HTTP')) {
            $mode = TK_BACKEND;
            $backend_dir = $config->item_get('backend_dir', 'HTTP');
        // Case 3 frontend: /{language_prefix}/...
        } else {
            $mode = TK_FRONTEND;
            $backend_dir = '';
        // Case 4 frontend: /...
        }

        $this->mode = $mode;
        $this->backend_dir = $backend_dir;

        // Define Language prefix
        $params = $this->define_language_prefix($config, $this->mode, $params);

        // Parse routing
        if(!empty($params)) {

            $params = routing::parse($params);

            $this->addon = array_shift($params);

            if(!empty($params)) {
                $this->action = array_shift($params);
            } else {
                $this->action = $config->item_get($this->mode.'_default_callable_action', 'HTTP');
            }

            /*
             * Check, if application allowed to parse URLs with dashed segments.
             * Example: /addon-name-with-dashes/and-action-name/param-1/param-2
             * Will parse as:
             * addon: addon_name_with_dashes
             * action: and_action_name
             * params: param-1, param-2
             * Notice: in routes configuration dashes is allowed by default.
             */
            if($config->item_get('http_allow_url_dashes', 'HTTP') == 1) {
                $this->addon = str_replace('-', '_', $this->addon);
                $this->action = str_replace('-', '_', $this->action);
            }

        } else {

            $this->addon = $config->item_get($this->mode.'_default_callable_addon', 'HTTP');
            $this->action = $config->item_get($this->mode.'_default_callable_action', 'HTTP');

        }

        $this->params = $params;

        self::$initialized = true;

        return true;

    } // End func init

    /**
     * Load language prefix
     * Return true if language prefix parsed from URL
     *
     * @access protected
     * @param object $config
     * @param string $mode
     * @param array $params
     * @return array
     * @since Version 4.0.0
     */
    protected function define_language_prefix($config, $mode, $params = NULL) {

        // Get allowed languages by mode
        $this->allowed_languages = explode('|', $config->item_get('http_'.$mode.'_allowed_languages', 'HTTP'));

        // Get default language prefix by mode
        $language_prefix = $config->item_get('http_'.$mode.'_default_language', 'HTTP');

        // Try to set language from browser if allowed
        if($config->item_get('http_catch_browser_language', 'HTTP') == 1) {
            
        	$language_prefix = $this->matches_browser_language($this->allowed_languages);
            
        	// Language prefix not match
            if(!$language_prefix) {
	            $language_prefix = $config->item_get('http_'.$mode.'_default_language', 'HTTP');
            }
            
        }
	    
        $this->language_prefix = $language_prefix;
        $this->language_parsing = $config->item_get('http_parse_language', 'HTTP');

        // Do not parsing language from URL
        if(!$this->language_parsing) {

            // Remove backend dir if in backend mode and valid.
            if($mode == TK_BACKEND and isset($params[0]) and $params[0] == $config->item_get('backend_dir', 'HTTP')) {
                array_shift($params);
            }

            return $params;
        }

        // Check, if URL Detected language prefix
        if(isset($params[0]) and in_array($params[0], $this->allowed_languages)) {

            $this->language_prefix = $params[0];
            array_shift($params);

        }

        // Remove backend dir if in backend mode.
        if($mode == TK_BACKEND and isset($params[0]) and $params[0] == $config->item_get('backend_dir', 'HTTP')) {
            array_shift($params);
        }

        return $params;

    } // End func define_language_prefix

    /**
     * Return subdomain if exists in host name.
     *
     * @access public
     * @return mixed string | bool
     */
    public function subdomain() {

        $tmp = explode('.', $this->lib->filter->server('HTTP_HOST'));

        if(count($tmp) == 3) {
            return $tmp[0];
        }

        return false;

    } // End func subdomain

    /**
     * Return detected language prefix
     *
     * @access public
     * @return string
     */
    public function language_prefix() {
        return $this->language_prefix;
    }

    /**
     * Return allowed languages
     *
     * @access public
     * @return array
     * @since Version 4.0.0
     */
    public function allowed_languages() {
        return $this->allowed_languages;
    }

    /**
     * Return true if language parsing from query string
     *
     * @access public
     * @return bool
     */
    public function language_parsing() {
        return $this->language_parsing;
    }

    /**
     * Return mode for application frontend | backend
     *
     * @access public
     * @return string
     */
    public function mode() {
        return $this->mode;
    }

    /**
     * Return backend directory name defined in application configuration
     *
     * @access public
     * @return string
     */
    public function backend_dir() {
        return $this->backend_dir;
    }

    /**
     * Return base url
     *
     * @access public
     * @return string
     */
    public function base_url() {
        return $this->base_url;
    }

    /**
     * Return detected addon
     *
     * @access public
     * @return string
     */
    public function addon() {
        return $this->addon;
    }

    /**
     * Return detected action
     *
     * @access public
     * @return string
     */
    public function action() {
        return $this->action;
    }

    /**
     * Return query string
     *
     * @access public
     * @return string
     */
    public function query_string() {
        return $this->query_string;
    }

    /**
     * Return true if the request protocol is https
     *
     * @access public
     * @return bool
     * @since version 2.5.0
     */
    public function is_https() {

        if($this->lib->filter->server('HTTPS') and $this->lib->filter->server('HTTPS') != 'off') {
            return true;
        } else {
            return false;
        }

    } // End func is_https

    /**
     * Generate and return server url
     *
     * @access public
     * @return string
     */
    public function dynamic_url() {

        $base_url = '';

        if($this->is_https()) {
            $base_url .= 'https://';
        } else {
            $base_url .= 'http://';
        }

        $base_url .= $this->lib->filter->server('HTTP_HOST');

        return $base_url;

    } // end func dynamic_url

    /**
     * Return exploded parts from url
     *
     * @access public
     * @param int $index
     * @return mixed
     * @since version 2.3.0
     */
    public function parts($index = NULL) {

        if(is_null($index)) {
            return $this->parts;
        }

        if(isset($this->parts[$index])) {
            return $this->parts[$index];
        }

        return false;

    } // End func parts

    /**
     * Return count of url parts
     *
     * @access public
     * @return integer
     * @since version 2.3.0
     */
    public function parts_count() {
        return count($this->parts);
    }

    /**
     * Return parameter value by item or parameters array
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function params($item = NULL) {

        if(is_null($item)) {
            return $this->params;
        }

        if(isset($this->params[$item])) {
            return $this->params[$item];
        }

        return false;

    } // end func params

    /**
     * Return count of parameters
     *
     * @access public
     * @return integer
     */
    public function params_count() {
        return count($this->params);
    }

    /**
     * Check, if the browser languages allowed for application.
     *
     * @access protected
     * @param array $allowed_languages
     * @return string | bool
     * @since v.2.1.0
     */
    protected function matches_browser_language($allowed_languages) {

        $b_languages = $this->lib->client->languages();

        if(empty($b_languages)) {
            return false;
        }

        foreach($b_languages as $language) {

            $tmp = explode('-', $language);
            $prefix = $tmp[0];
	        
            if(in_array($prefix, $allowed_languages)) {
                return $prefix;
            }

        }

        return false;

    } // End func matches_browser_language

    /**
     * Return url
     *
     * @access public
     * @param mixed $id_addon
     * @param mixed $action
     * @param mixed $params_set
     * @return string
     */
    public function url($id_addon = NULL, $action = NULL, $params_set = NULL) {

        /* Set base url */
        $url = $this->base_url();

        /* Append language prefix if enabled */
        if($this->language_parsing() == true) {
            $url .= '/' . $this->language_prefix();
        }

        /*
         * Append backend dir (administrator control panel name)
         * if in backend mode, and backend_dir is not empty
         */
        if($this->mode() == TK_BACKEND and $this->backend_dir() != '') {
            $url .= '/' . $this->backend_dir();
        }

        /* Append id_addon to url */
        if($id_addon === true) {
            $url .= '/' . $this->addon();
        } elseif(trim($id_addon) != '') {
            $url .= '/' . $id_addon;
        }

        /* Append action to url */
        if($action === true) {
            $url .= '/' . $this->action();
        } elseif(trim($action) != '') {
            $url .= '/' . $action;
        }

        $params_arr = array();

        /* Append parameters */
        if($params_set === true) {
            $params_arr = $this->params();
        } elseif(!empty($params_set) and is_array($params_set)) {
            $params_arr = $params_set;
        }

        if(count($params_arr) > 0) {
            foreach($params_arr as $param) {
                $url .= '/' . $param;
            }
        }

        // Remove last slash
        $url = rtrim($url, '/');

        return $url;

    } // end func url

    /**
     * Set language prefix
     *
     * @access public
     * @param string $lp
     * @return void
     */
    public function set_language_prefix($lp) {

        if(!in_array($lp, $this->allowed_languages)) {
            $this->language_prefix = $lp;
            return true;
        }

        return false;

    } // End func set_language_prefix

    /**
     * Set addon
     *
     * @access public
     * @param string $addon
     * @return bool
     */
    public function set_addon($addon) {

        if(trim($addon) != '') {
            $this->addon = $addon;
            return true;
        }

        return false;

    } // end func set_addon

    /**
     * Set action
     *
     * @access public
     * @param string $action
     * @return bool
     */
    public function set_action($action) {

        if(trim($action) != '') {
            $this->action = $action;
            return true;
        }

        return false;

    } // end func set_action

    /**
     * Set application mode
     *
     * @access public
     * @param string $mode 'frontend' | 'backend'
     * @return void
     */
    public function set_mode($mode) {

        if($mode != TK_BACKEND and $mode != TK_FRONTEND) {
            trigger_error('Can set application mode only as `' . TK_BACKEND . '` or `'.TK_FRONTEND.'` !', E_USER_ERROR);
        }

        $this->mode = $mode;
    }

} /* End of class url_lib */