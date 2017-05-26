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

    protected $request;
    
    /**
     * Class constructor
     *
     * @access public
     */
    public function  __construct() {
        $this->lib = lib::instance();
        $this->request = request::instance();
    } // end constructor

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
        $url = $this->request->base_url();

        /* Append language prefix if enabled */
        if($this->request->language_parsing() == true) {
            $url .= '/' . $this->request->language_prefix();
        }

        /*
         * Append interface dir
         *
         * @todo refactor this. should get interface path
         */
        
        //if($this->request->mode() == 'zzzzz' and $this->request->interface_dir() != '') {
        //    $url .= '/' . $this->request->interface_dir();
        //}

        /* Append id_addon to url */
        if($id_addon === true) {
            $url .= '/' . $this->request->addon();
        } elseif(trim($id_addon) != '') {
            $url .= '/' . $id_addon;
        }

        /* Append action to url */
        if($action === true) {
            $url .= '/' . $this->request->action();
        } elseif(trim($action) != '') {
            $url .= '/' . $action;
        }

        $params_arr = array();

        /* Append parameters */
        if($params_set === true) {
            $params_arr = $this->request->url_params();
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

} /* End of class url_lib */