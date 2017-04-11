<?php
/**
 * toKernel - Universal PHP Framework.
 * SQLite3 query result class library
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
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * db_mysql_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_sqlite_query_result_lib {

	/**
	 * SQLite3 Object
	 *
	 * @access protected
	 * @var object
	 */
	protected $sqlite3;

	/**
	 * Result object
	 *
	 * @access protected
	 * @var object
	 */
	protected $result;

	/**
	 * Initial value for number of rows
	 *
	 * @access protected
	 * @var int
	 */
	protected $num_rows = NULL;

	/**
	 * Initial value of rows
	 *
	 * @access protected
	 * @var array
	 */
	protected $rows = array();

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param object $sqlite3
	 * @param object $result
	 */
	public function __construct($sqlite3, $result) {
		$this->sqlite3 = $sqlite3;
		$this->result = $result;
	}

	/**
	 * Initialize result with number of rows and assoc array
	 *
	 * @access protected
	 * @return mixed
	 */
	protected function initialize() {

		if($this->num_rows !== NULL) {
			return true;
		}

		$this->result->reset();

		if ($this->result->numColumns() or $this->result->columnType(0) != SQLITE3_NULL) {

			while($row = $this->result->fetchArray(SQLITE3_ASSOC)) {

				$this->rows[] = $row;
				$this->num_rows ++;
			}

		} else {
			$this->num_rows = 0;
		}

		$this->result->reset();

	} // End func initialize

	/**
	 * Return affected rows of query
	 *
	 * @access public
	 * @return int
	 */
	public function affected_rows() {
		return $this->sqlite3->changes();
	}

	/**
	 * Return number of rows from query result
	 *
	 * @access public
	 * @return int
	 */
	public function num_rows() {

		$this->initialize();

		return $this->num_rows;

	} // End func num_rows

	/**
	 * Return number of table fields from query result
	 *
	 * @access public
	 * @return int
	 */
	public function num_fields() {
		return $this->result->numColumns();
	}

	/**
	 * Return array with result objects
	 *
	 * @access public
	 * @return array | bool
	 */
	public function result_object() {

		$this->initialize();

		if(!$this->num_rows) {
			return false;
		}

		$ret_arr = array();

		foreach($this->rows as $row) {

			$object = new stdClass();

			foreach($row as $field => $value) {
				$object->$field = $value;
			}

			$ret_arr[] = $object;

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

		$this->initialize();

		if(!$this->num_rows) {
			return false;
		}

		return $this->rows;

	} // End func result_array


	/**
	 * Return row as object
	 *
	 * @access public
	 * @return bool | null | object
	 */
	public function result_row_object() {

		$this->initialize();

		if(!$this->num_rows) {
			return false;
		}

		$object = new stdClass();

		foreach($this->rows[0] as $field => $value) {
			$object->$field = $value;
		}

		return $object;

	} // End func result_row_object

	/**
	 * Return row as array
	 *
	 * @access public
	 * @return bool | null | array
	 */
	public function result_row_array() {

		$this->initialize();

		if(!$this->num_rows) {
			return false;
		}

		return $this->rows[0];

	} // End func result_row_array

	/**
	 * Free the result resource
	 *
	 * @access public
	 * @return bool
	 */
	public function free_result() {

		if($this->result) {
			$this->result->finalize();
			return true;
		}

		return false;

	} // End func free_result

} /* End of class db_sqlite_query_result_lib */