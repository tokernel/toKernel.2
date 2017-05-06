<?php
/**
 * toKernel - Universal PHP Framework.
 * Routing class for HTTP and CLI mode
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
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Routing class for HTTP and CLI mode
 *
 * @author David A. <tokernel@gmail.com>
 */
class routing {
		
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access private
	 * @var object
	 */
	private static $lib;

	/**
	 * Arguments array
	 *
	 * @access private
	 * @var array
	 */
	private static $args;
	
	/**
	 * Routes configuration object
	 *
	 * @access private
	 * @var object
	 */
	private static $routes_ini;
	
	/**
	 * Status of this object initialization
	 *
	 * @access private
	 * @var bool
	 */
	private static $initialized = false;
	
	/**
	 * Parse routing
	 *
	 * @static
	 * @access public
	 * @param array $q_arr
	 * @return array|bool
	 */
	static public function parse($q_arr) {
		
		if(!self::$initialized) {
			self::$lib = lib::instance();
			// Load routes initialization file
			self::$routes_ini = self::$lib->ini->instance(TK_APP_PATH . 'config' . TK_DS . TK_ROUTES_INI);
		}
		
		// Cleanup array values
		$q_arr = self::clean($q_arr);

		$routes = self::$routes_ini->section_get('ROUTING');
		$nqs = array();

		// Parse each route to detect matching
		foreach($routes as $item => $value) {

			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));

			$nqs = self::compare_route($q_arr, $r_arr, $v_arr);

			if($nqs !== false) {
				self::$args = $nqs;
				return $nqs;
			}

		}

		self::$args = $nqs;
		self::$initialized = true;
		
		return $q_arr;

	} // End func parse

	/**
	 * Cleanup array arguments
	 *
	 * @static
	 * @access private
	 * @param array $q_arr
	 * @return array
	 */
	private static function clean($q_arr) {

		foreach($q_arr as $index => $value) {

			if(trim($value) == '') {
				unset($q_arr[$index]);
			}

		}

		return $q_arr;

	} // End func clean

	/**
	 * Compare route to detect matching
	 *
	 * @static
	 * @access private
	 * @param array $q_arr
	 * @param array $r_arr
	 * @param array $v_arr
	 * @return mixed array|bool
	 */
	private static function compare_route($q_arr, $r_arr, $v_arr) {

		if(count($q_arr) != count($r_arr)) {
			return false;
		}

		$vars = array();

		for($i = 0; $i < count($q_arr); $i++) {

			$var = self::is_var($r_arr[$i]);

			if($var !== false) {

				$add = false;

				// Check if var is valid by required
				if($r_arr[$i] == '{var.id}') {
					if(self::$lib->valid->id($q_arr[$i])) {
						$add = true;
					}
				} elseif($r_arr[$i] == '{var.num}') {
					if(is_numeric($q_arr[$i])) {
						$add = true;
					}
				} elseif($r_arr[$i] == '{var.any}') {
					$add = true;
				}

				if($add == true) {
					$vars[] = $q_arr[$i];
					$q_arr[$i] = $r_arr[$i];
				}

			}

		}

		if(implode('/', $q_arr) == implode('/', $r_arr)) {

			$nqs = array();
			$vars_set = 0;

			foreach($v_arr as $val) {

				if(substr($val, 0, 5) == '{var}' and isset($vars[$vars_set])) {
					$nqs[] = $vars[$vars_set];
					$vars_set++;
				} else {
					$nqs[] = $val;
				}

			}

			return $nqs;
		}

		return false;

	} // End func compare_route

	/**
	 * Check if the given value is route defined variable
	 *
	 * @static
	 * @access private
	 * @param string $str
	 * @return bool
	 */
	private static function is_var($str) {

		$vars = array(
			'{var.id}' => 'Integer',
			'{var.num}' => 'Number',
			'{var.any}' => 'Any value'
		);

		if(isset($vars[$str])) {
			return $vars[$str];
		}

		return false;

	} // End func is_var

} /* End class routing */