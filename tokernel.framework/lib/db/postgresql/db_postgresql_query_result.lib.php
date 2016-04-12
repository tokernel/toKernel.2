<?php
/**
 * toKernel - Universal PHP Framework.
 * PostgreSQL query result class library
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
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * db_postgresql_query_result_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_postgresql_query_result_lib {

	/**
	 * Connection resource
	 *
	 * @access protected
	 * @var null
	 */
	protected $conn;

	/**
	 * Result object
	 *
	 * @access protected
	 * @var object
	 */
	protected $result;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param resource $conn
	 * @param object $result
	 * @return void
	 */
	public function __construct($conn, $result) {
		$this->conn = $conn;
		$this->result = $result;
	}

	/**
	 * Return affected rows of query
	 *
	 * @access public
	 * @return int
	 */
	public function affected_rows() {
		return pg_affected_rows($this->result);
	}

	/**
	 * Return number of rows from query result
	 *
	 * @access public
	 * @return int
	 */
	public function num_rows() {
		return pg_num_rows($this->result);
	}

	/**
	 * Return number of table fields from query result
	 *
	 * @access public
	 * @return int
	 */
	public function num_fields() {
		return pg_num_fields($this->result);
	}

	/**
	 * Return array with result objects
	 *
	 * @access public
	 * @return array | bool
	 */
	public function result_object() {

		if(!$this->result) {
			return false;
		}

		$ret_arr = array();

		while($obj = pg_fetch_object($this->result)) {
			$ret_arr[] = $obj;
		}

		return $ret_arr;

	} // End func result_object

	/**
	 * Return array with result arrays
	 *
	 * @access public
	 * @return array | bool
	 */
	public function result_array() {

		if(!$this->result) {
			return false;
		}

		return  pg_fetch_all($this->result);

	} // End func result_array


	/**
	 * Return row as object
	 *
	 * @access public
	 * @return bool | null | object
	 */
	public function result_row_object() {

		if(!$this->result) {
			return false;
		}

		return pg_fetch_object($this->result);

	} // End func result_row_object

	/**
	 * Return row as array
	 *
	 * @access public
	 * @return bool | null | array
	 */
	public function result_row_array() {

		if(!$this->result) {
			return false;
		}

		return pg_fetch_assoc($this->result);

	} // End func result_row_array

	/**
	 * Free the result resource
	 *
	 * @access public
	 * @return bool | void
	 */
	public function free_result() {

		if($this->result) {
			return pg_free_result($this->result);
		}

		return false;

	} // End func free_result

} // End of class db_postgresql_query_result_lib

// End of file
?>