<?php
/**
 * toKernel - Universal PHP Framework.
 * Universal data generator class
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
 * @version    1.1.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * generator_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class generator_lib {

	/**
	 * Create random generated number
	 *
	 * @access public
	 * @param  int $length
	 * @return int
	 * @since  1.1.0
	 */
	public function create_number($length) {

		$min = 1 . str_repeat(0, $length - 1);
		$max = str_repeat(9, $length);
		return mt_rand($min, $max);

	} // End func create_number

	/**
	 * Create random generated password
	 *
	 * @access public
	 * @param  int $min
	 * @param  int $max
	 * @param  bool $uppercase
	 * @return string
	 */
	public function create_password($min = 6, $max = 15, $uppercase = true) {

		return $this->create_string($min, $max, $uppercase, true, true);

	} // end func create_password

	/**
	 * Create random generated username
	 *
	 * @access public
	 * @param  int $min
	 * @param  int $max
	 * @return string
	 */
	public function create_username($min = 6, $max = 12) {

		$str = $this->create_name($min, $max, false, '-.');
		$is_num = mt_rand(0, 1);

		if($is_num == true and strlen($str) < $max and strpos($str, '.') === false and strpos($str, '-') == false) {
			$str .= mt_rand(11, 999);
		}


		return $str;

	} // end func create_username

	/**
	 * Create random generated email address
	 *
	 * @access public
	 * @param  string $domain = NULL
	 * @return string
	 */
	public function create_email($domain = NULL) {

		$min = 3;
		$max = 12;

		// Generate domain if null.
		if(is_null($domain)) {
			$domain = $this->create_domain();
		}

		$str = $this->create_name($min, $max, false, '-_.');

		// Set at and domain.
		$str .= '@' . $domain;

		return $str;

	} // end func create_email

	/**
	 * Create random generated domain name
	 *
	 * @access public
	 * @param  string $country = NULL
	 * @return string
	 */
	public function create_domain($country = NULL) {

		$min = 3;
		$max = 12;

		// Generate $country if null.
		if(is_null($country)) {
			$countries_arr = array('com', 'org', 'net', 'gov', 'ru', 'am', 'fr');
			$country = $countries_arr[array_rand($countries_arr)];
		}

		// Set first char, that cannot be symbol.
		$str = $this->create_name($min, $max, false, '-.');

		// Set country prefix
		$str .= '.' . $country;

		return $str;

	} // end func create_domain

	/**
	 * Create random string a-z, A-Z, 0-9, and symbols.
	 *
	 * @access public
	 * @param  int $min
	 * @param  int $max
	 * @param  bool $uppercase = true
	 * @param  bool $numbers = true
	 * @param  bool $symbols = true
	 * @param  string $chars_allowed = NULL
	 * @return string
	 */
	public function create_string($min = 6, $max = 12, $uppercase = true, $numbers = true, $symbols = true, $chars_allowed = NULL) {

		$chars_alpha = "abcdefghijklmnopqrstuvwxyz";
		$chars_alpha_upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$chars_numbers = "0123456789";
		$chars_symbols = "~`!@#$%^&*()_|=-.,?;:]}[}<>";

		$chars = $chars_alpha;

		if($uppercase == true) {
			$chars .= $chars_alpha_upper;
		}

		if($numbers == true) {
			$chars .= $chars_numbers;
		}

		if($symbols == true) {
			$chars .= $chars_symbols;
		}

		if(!is_null($chars_allowed)) {
			$chars .= $chars_allowed;
		}

		srand((double)microtime()*1000000);

		$i = 0;

		$str = '';

		$length = mt_rand($min, $max);
		$chars_count = 0;

		while ($i <= $length) {
			$num = mt_rand(0, (strlen($chars) - 1));

			$random_char = substr($chars, $num, 1);

			if(strpos($chars_symbols, $random_char) === false) {
				$str .= $random_char;
				$i++;
			} else {
				if($chars_count < 2) {
					$str .= $random_char;
					$i++;
					$chars_count++;
				}
			}

			//$i++;
		}

		return $str;

	} // End func create_string

	/**
	 * Create random sentence by given text.
	 *
	 * @access public
	 * @param  string $str
	 * @return string
	 */
	public function create_sentence($str = NULL) {

		if(is_null($str)) {
			$str = "{Hello!|Hi!|Hola!} {maybe|actually|fortunately} this is {your|our|my} {best|nice|well|cool|lucky} {chance|option|case|moment} {to|for} {make|take|get|generate|create|give|catch|bring} a random {sentence|string|expression}.";
		}

		$pattern = "/{([^{}]*)}/";

		while (preg_match_all($pattern, $str, $matches) > 0) {
			for ($i = 0; $i < count($matches[1]); $i++) {

				$options = explode('|', $matches[1][$i]);
				$rand_option = $options[rand(0, count($options)-1)];
				$str = str_replace($matches[0][$i], $rand_option, $str);

			}
		}

		return $str;

	} // End func create_sentence

	/**
	 * Create random date between two dates.
	 *
	 * @access public
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $format
	 * @return string
	 */
	public function create_date($start_date, $end_date, $format = 'Y-m-d H:i:s') {

		$d1 = strtotime($start_date);
		$d2 = strtotime($end_date);

		$random = mt_rand($d1, $d2);

		return date($format, $random);

	} // End func create_date

	/**
	 * Generate name
	 *
	 * @access public
	 * @param int $min
	 * @param int $max
	 * @param bool $uc_first
	 * @param string $chars
	 * @return string
	 */
	public function create_name($min = 3, $max = 8, $uc_first = true, $chars = '') {

		$a = 'AEIOU'; // W
		$b = 'BCDFGHJKLMNPQRSTVXYZ';

		$start = mt_rand(1, 2);

		if($start == 1) {
			$string = $a;
		} else {
			$string = $b;
		}

		$name = '';

		$name_len = mt_rand($min, $max);
		$half = 0;
		$char = '';
		$is_char = mt_rand(0, 1);

		if($name_len >= 5 and $chars != '' and $is_char > 0) {
			$half = ceil($name_len / 2);

			if(strlen($chars) == 1) {
				$char = $chars;
			} else {
				$char = substr(
					$chars,
					mt_rand(0, strlen($chars) - 1),
					1
				);
			}
		}

		for($i = 1; $i <= $name_len; $i++) {

			if($half > 0 and $char != '' and $half == $i) {
				$name .= $char;
			} else {
				$name .= substr(
					$string,
					mt_rand(0, strlen($string) - 1),
					1
				);
			}

			if($string == $a) {
				$string = $b;
			} else {
				$string = $a;
			}
		}

		$name = strtolower($name);

		if($uc_first == true) {
			$name = ucfirst($name);
		}

		return $name;

	} // End func create_name

	/**
	 * Create IP Address
	 *
	 * @access public
	 * @return string;
	 */
	public function create_ip() {

		$str = '';
		$str .= mt_rand(100, 192);
		$str .= '.';
		$str .= mt_rand(95, 168);
		$str .= '.';
		$str .= mt_rand(0, 100);
		$str .= '.';
		$str .= mt_rand(0, 100);

		return $str;

	} // End func create_ip

} /* End of class generator_lib */