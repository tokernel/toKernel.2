<?php
/**
 * toKernel - Universal PHP Framework.
 * SQLite3 database class library uses PHP SQLite3 extension
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
 * db_sqlite_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_sqlite_lib extends db_base_lib {

	/**
	 * SQLite3 object
	 *
	 * @access protected
	 * @var null
	 */
	protected $sqlite3 = NULL;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config) {

		parent::__construct($config);

		$this->debug_log(__CLASS__ . '->' . __FUNCTION__ . '() Adapter SQLite3 initialized.');
	}

	/**
	 * Class destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		$this->close();
		$this->sqlite3 = NULL;
		parent::__destruct();
	}

	/**
	 * Connect to database
	 *
	 * @access public
	 * @return bool
	 */
	public function connect() {

		// Return true if already connected
		if($this->sqlite3 and is_object($this->sqlite3) === true) {
			return true;
		}

		// Check if SQLite3 extension exists
		if(!class_exists('SQLite3')) {
			$this->error = "The SQLite3 extension is not available.";
			trigger_error($this->error, E_USER_ERROR);
			return false;
		}

		// Open database file or memory (as defined in configuration)
		try {

			if($this->config['open_readonly'] == 1) {
				$this->sqlite3 = new SQLite3($this->config['database'], SQLITE3_OPEN_READONLY);
			} else {
				$this->sqlite3 = new SQLite3($this->config['database']);
			}

		} catch(Exception $e) {
			$this->error = $e->getMessage();
			tk_e::error(E_USER_ERROR, 'SQLite3: ' . $e->getMessage(),__FILE__,__LINE__);
		}

		$this->debug_log('Opened SQLite3 database: ' . $this->config['database'] . ' successfully.');

		return true;

	} // End func connect

	/**
	 * Disconnect from server
	 *
	 * @access public
	 * @return bool
	 */
	public function close() {

		if ($this->sqlite3 and is_object($this->sqlite3) === true) {
			$this->sqlite3->close();

			$this->debug_log('Closed SQLite3 database: ' . $this->config['database']);
			return true;
		}

		return false;

	} // End func close

	/**
	 * Insert many records into db table
	 * Returns inserted records count (affected rows)
	 *
	 * @access public
	 * @param string $table_name
	 * @param array $fields_array
	 * @param array $values_array
	 * @return int
	 */
	public function insert_batch($table_name, array $fields_array, array $values_array) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);
		$this->check_arr($fields_array);
		$this->check_arr($values_array);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = "INSERT INTO " . $table_name . " (";

		$query_string .= implode(', ', array_values($fields_array));

		$query_string .= ') VALUES ';

		foreach($values_array as $values) {

			$query_string .= '(';

			foreach($values as $value) {
				$query_string .= "'" . $this->sqlite3->escapeString($value) . "', ";
			}

			$query_string = rtrim($query_string, ', ');
			$query_string .= '), ';

		}

		$query_string = rtrim($query_string, ', ');

		$result = $this->sqlite3->exec($query_string);

		if(!$result) {
			$this->error = $this->sqlite3->lastErrorMsg();
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = $this->sqlite3->changes();

		$this->debug_log('SQLite3 insert_batch() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

		return $affected_rows;

	} // End func insert_batch

	/**
	 * Insert records into db table
	 * Returns last insert id
	 *
	 * @access public
	 * @param string $table_name
	 * @param array $values_array
	 * @return int
	 */
	public function insert($table_name, array $values_array) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);
		$this->check_arr($values_array);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = "INSERT INTO " . $table_name . " ( ";

		$query_string .= implode(',', array_keys($values_array));

		$query_string .= ') VALUES (';

		foreach($values_array as $field => $value) {
			$query_string .= "'" . $this->sqlite3->escapeString($value) . "', ";
		}

		$query_string = rtrim($query_string, ', ');

		$query_string .= ')';

		$result = $this->sqlite3->exec($query_string);

		if(!$result) {
			$this->error = $this->sqlite3->lastErrorMsg();
			trigger_error($this->error, E_USER_ERROR);
		}

		$insert_id = $this->sqlite3->lastInsertRowID();

		$this->debug_log('SQLite3 insert() success : ' . $query_string . ' / insert id: ' . $insert_id);

		return $insert_id;

	} // End func insert

	/**
	 * Return last insert id
	 *
	 * @access public
	 * @return int|string
	 */
	public function insert_id() {

		// Check connection
		$this->connect();

		return $this->sqlite3->lastInsertRowID();

	} // End func insert_id

	/**
	 * Update records in db table
	 * Returns affected rows count
	 *
	 * @access public
	 * @param string $table_name
	 * @param array $values_array
	 * @param array $where
	 * @return int
	 */
	public function update($table_name, array $values_array, array $where) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);
		$this->check_arr($values_array);
		$this->check_arr($where);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = "UPDATE " . $table_name . " SET ";

		foreach($values_array as $field => $value) {
			$query_string .= $field . " = '" . $this->sqlite3->escapeString($value) . "', ";
		}

		$query_string = rtrim($query_string, ', ');

		$query_string .= ' WHERE ';

		foreach($where as $field => $value) {
			$query_string .= $field . " = '" . $this->sqlite3->escapeString($value) . "' and ";
		}

		$query_string = rtrim($query_string, 'and ');

		$result = $this->sqlite3->exec($query_string);

		if(!$result) {
			$this->error = $this->sqlite3->lastErrorMsg();
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = $this->sqlite3->changes();

		$this->debug_log('SQLite3 update() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

		return $affected_rows;

	} // End func update

	/**
	 * Delete records in db table
	 * Returns affected rows count
	 *
	 * @access public
	 * @param string $table_name
	 * @param array $where
	 * @return int
	 */
	public function delete($table_name, array $where) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);
		$this->check_arr($where);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = "DELETE FROM " . $table_name . " WHERE ";

		foreach($where as $field => $value) {
			$query_string .= $field . " = '" . $this->sqlite3->escapeString($value) . "' and ";
		}

		$query_string = rtrim($query_string, 'and ');

		$result = $this->sqlite3->exec($query_string);

		if(!$result) {
			$this->error = $this->sqlite3->lastErrorMsg();
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = $this->sqlite3->changes();

		$this->debug_log('SQLite3 delete() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

		return $affected_rows;

	} // End func delete

	/**
	 * Run SQLite3 Query
	 *
	 * This method will return SQLite3 Query result object if it's selects records
	 * See: db_sqlite_query_result class
	 *
	 * @access public
	 * @param string $query_string
	 * @param mixed array | null $values_array
	 * @return object db_sqlite_query_result
	 */
	public function query($query_string, array $values_array = NULL) {

		// Check connection
		$this->connect();

		// Bind query values from array
		if(is_array($values_array) and !empty($values_array)) {

			foreach($values_array as $value) {

				$pos = strpos($query_string, '?');
				if ($pos !== false) {
					$replace = "'" . $this->sqlite3->escapeString($value) . "'";
					$query_string = substr_replace($query_string, $replace, $pos, 1);
				}

			}

		}

		$result = $this->sqlite3->query($query_string);

		if(!$result) {
			$this->error = $this->sqlite3->lastErrorMsg();
			trigger_error($this->error, E_USER_ERROR);
		}

		return new db_sqlite_query_result_lib($this->sqlite3, $result);

	} // End func query

	/**
	 * Select all records from table
	 *
	 * @access public
	 * @param string $table_name
	 * @param mixed - array | null $fields_array
	 * @return object
	 */
	public function select_all($table_name, array $fields_array = NULL) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = " SELECT ";

		if(!is_null($fields_array)) {
			$query_string .= implode(', ', $fields_array) . ' ';
		} else {
			$query_string .= ' * ';
		}

		$query_string .= " FROM `" . $table_name . "` ";

		return $this->query($query_string);

	} // End func select_all

	/**
	 * Select records from table by where expression with equal clause
	 *
	 * @access public
	 * @param string $table_name
	 * @param mixed - array | null $fields_array
	 * @param mixed - array | null $where_data
	 * @return object
	 */
	public function select($table_name, array $fields_arr = NULL, array $where_data = NULL) {

		// Check connection
		$this->connect();

		// Check parameters
		$this->check_table_name($table_name);

		// Set table prefix
		$table_name = $this->config['table_prefix'] . $table_name;

		// Build query
		$query_string = " SELECT ";

		if(!empty($fields_arr)) {
			$query_string .= implode(', ', $fields_arr);
		} else {
			$query_string .= ' * ';
		}

		$query_string .= " FROM `" . $table_name . '` ';

		if(!empty($where_data)) {

			$query_string .= " WHERE ";

			foreach($where_data as $field => $value) {
				$query_string .= $field . " = '" . $this->sqlite3->escapeString($value) . "' and ";
			}

			$query_string = rtrim($query_string, 'and ');
		}

		return $this->query($query_string);

	} // End func select

	/**
	 * Escape string
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public function escape($value) {

		// Check connection
		$this->connect();

		return $this->sqlite3->escapeString($value);

	} // End func escape

	/**
	 * Begin transaction
	 *
	 * @access public
	 * @param bool $auto_commit = false
	 * @return bool
	 */
	public function begin_trans($auto_commit = false) {

		// Check connection
		$this->connect();

		return $this->sqlite3->exec('BEGIN;');

	} // End func begin_trans

	/**
	 * Commit transaction
	 *
	 * @access public
	 * @return bool
	 */
	public function commit_trans() {

		// Check connection
		$this->connect();

		return $this->sqlite3->exec('COMMIT;');

	} // End func commit_trans

	/**
	 * Roll back transaction
	 *
	 * @access public
	 * @return bool
	 */
	public function rollback_trans() {

		// Check connection
		$this->connect();

		return $this->sqlite3->exec('ROLLBACK;');

	} // End func rollback_trans

} /* End of class db_sqlite_lib */