<?php
/**
 * toKernel - Universal PHP Framework.
 * Universal string processing class library
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * string_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class string_lib {

	/**
	 * Change any url to url tag
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @since 1.0.0
	 */
	function url2tag($string) {

		/* Regular Expression filter */
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

		/* Check if there is a url in the string */
		if(preg_match($reg_exUrl, $string, $url)) {
			return preg_replace($reg_exUrl, '<a href="'.$url[0].'">'.$url[0].'</a> ', $string);
		} else {
			return $string;
		}

	} // End func url2tag

	/**
	 * Crop a part from head of string without damaging words by catching last space character.
	 *
	 * @access public
	 * @param string $str
	 * @param int $char_count
	 * @param bool $suspension_points = true
	 * @return string
	 * @since 1.0.0
	 */
	public function head($str, $char_count, $suspension_points = true) {

		if(mb_strlen($str) <= $char_count) {
			return $str;
		}

		// Crop the string with plus one char.
		$str = mb_substr($str, 0, ($char_count + 1));

		// Check, if last char is space
		if(mb_substr($str, -1) == ' ') {

			// It is space, so no words damaged.
			// Return string without space char.
			return trim($str);
		}

		// Get last space position in string
		$space_pos = mb_strrpos($str, ' ');

		// Last space found.
		if($space_pos !== false) {

			// Crop string before last space.
			$str = mb_substr($str, 0, $space_pos);
		}

		// Add suspension points is true
		if($suspension_points == true) {
			$str .= '...';
		}

		return $str;

	} // End func head

	/**
	 * Crop a part from end of string without damaging words by catching last space character.
	 *
	 * @access public
	 * @param string $str
	 * @param int $char_count
	 * @return string
	 * @since 1.0.0
	 */
	public function tail($str, $char_count) {

		if(mb_strlen($str) <= $char_count) {
			return $str;
		}

		// Crop the string with plus one char.
		$str = mb_substr($str, -($char_count + 1));

		// Check, if first char is space
		if(mb_substr($str, 0, 1) == ' ') {

			// It is space, so no words damaged.
			// Return string without space char.
			return trim($str);
		}

		// Get first space position in string
		$space_pos = mb_strpos($str, ' ');

		// Fist space found.
		if($space_pos !== false) {
			// Crop string from last space.
			return mb_substr($str, $space_pos, $char_count);
		}

		return $str;

	} // End func tail
	
} /* End of class string_lib */