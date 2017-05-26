<?php

/* 
 *Base Routing Class
 */

class routing_core {
				
	/**
	 * Cleanup array arguments
	 *
	 * @static
	 * @access protected
	 * @param array $q_arr
	 * @return array
	 */
	protected static function clean($q_arr) {

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
	 * @access protected
	 * @param array $q_arr
	 * @param array $r_arr
	 * @param array $v_arr
	 * @return mixed array|boolean
	 */
	protected static function compare_route($q_arr, $r_arr, $v_arr) {

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
					if(lib::instance()->valid->id($q_arr[$i])) {
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
	
}