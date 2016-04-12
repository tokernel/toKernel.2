<?php
/**
 * toKernel - Universal PHP Framework.
 * PostgreSQL database class library uses PHP PostgreSQL extension
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
 * db_postgresql_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_postgresql_lib extends db_base_lib {

	/**
	 * Connection resource
	 *
	 * @access protected
	 * @var null
	 */
	protected $conn = NULL;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public function __construct(array $config) {

		parent::__construct($config);

		$this->debug_log(__CLASS__ . '->' . __FUNCTION__ . '() Adapter PostgreSQL initialized.');
	}

	/**
	 * Class destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		$this->close();
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
		if($this->conn and pg_ping($this->conn) === true) {
			return true;
		}

		// Check if postgresql extension exists
		if(!function_exists('pg_connect')) {
			$this->error = "The PostgreSQL extension is not available.";
			trigger_error($this->error, E_USER_ERROR);
			return false;
		}

		// Build connection string
		$conn_string = "host=" . $this->config['host'];

		// Set port if defined
		if ($this->config['port'] != '') {
			$conn_string .= " port=" . $this->config['port'];
		}

		// Set dabase
		$conn_string .= " dbname=" . $this->config['database'];

		// Set account
		$conn_string .= " user=" . $this->config['username'] .
						" password=" . $this->config['password'];

		// Set sslmode if defined
		if($this->config['sslmode'] != '') {
			$conn_string .= " sslmode=".$this->config['sslmode'];
		}

		// Set encoding if defined
		if($this->config['charset'] != '') {
			$conn_string .= " options='--client_encoding=".$this->config['charset']."'";
		}

		// Connect to server
		$this->conn = @pg_connect($conn_string);

		if(!$this->conn) {
			$this->error = 'Unable to connect to PostgreSQL server';
			trigger_error($this->error, E_USER_ERROR);
		}

		$this->debug_log('Connected to PostgrSQL server: ' . $this->config['host'] . ' successfully.');
		$this->debug_log('Selected PostgrSQL Database: ' . $this->config['database'] . ' successfully.');

		return true;

	} // End func connect

	/**
	 * Disconnect from server
	 *
	 * @access public
	 * @return bool
	 */
	public function close() {

		if ($this->conn) {
			pg_close($this->conn);

			$this->debug_log('Closed PostgreSQL Server connection from: ' . $this->config['host']);
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
				$query_string .= "'" . pg_escape_string($this->conn, $value) . "', ";
			}

			$query_string = rtrim($query_string, ', ');
			$query_string .= '), ';
		}

		$query_string = rtrim($query_string, ', ');

		$result = pg_query($this->conn, $query_string);

		if(!$result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = pg_affected_rows($result);

		$this->debug_log('PostgreSQL insert_batch() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

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
			$query_string .= "'" . pg_escape_string($this->conn, $value) . "', ";
		}

		$query_string = rtrim($query_string, ', ');

		$query_string .= ')';

		$result = pg_query($this->conn, $query_string);

		if(!$result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		$insert_id = $this->insert_id();

		$this->debug_log('PostgreSQL insert() success : ' . $query_string . ' / insert id: ' . $insert_id);

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

		// Get last Insert ID
		$insert_result = pg_query("SELECT lastval();");

		if(!$insert_result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		$insert_row = pg_fetch_row($insert_result);
		return $insert_row[0];

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
			$query_string .= $field . " = '" . pg_escape_string($this->conn, $value) . "', ";
		}

		$query_string = rtrim($query_string, ', ');

		$query_string .= ' WHERE ';

		foreach($where as $field => $value) {
			$query_string .= $field . " = '" . pg_escape_string($this->conn, $value) . "' and ";
		}

		$query_string = rtrim($query_string, 'and ');

		$result = pg_query($this->conn, $query_string);

		if(!$result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = pg_affected_rows($result);

		$this->debug_log('PostgreSQL update() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

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
			$query_string .= $field . " = '" . pg_escape_string($this->conn, $value) . "' and ";
		}

		$query_string = rtrim($query_string, 'and ');

		$result = pg_query($this->conn, $query_string);

		if(!$result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		$affected_rows = pg_affected_rows($result);

		$this->debug_log('PostgreSQL delete() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

		return $affected_rows;

	} // End func delete

	/**
	 * Run PostgreSQL Query
	 *
	 * This method will return PostgreSQL Query result object if it's selects records
	 * See: db_postgresql_query_result class
	 *
	 * @access public
	 * @param string $query_string
	 * @param mixed array | null $values_array
	 * @return object db_postgresql_query_result_lib
	 */
	public function query($query_string, array $values_array = NULL) {

		// Check connection
		$this->connect();

		// Bind query values from array
		if(is_array($values_array) and !empty($values_array)) {

			foreach($values_array as $value) {

				$pos = strpos($query_string, '?');
				if ($pos !== false) {
					$replace = "'" . pg_escape_string($this->conn, $value) . "'";
					$query_string = substr_replace($query_string, $replace, $pos, 1);
				}

			}

		}

		$result = pg_query($this->conn, $query_string);

		if(!$result) {
			$this->error = pg_last_error($this->conn);
			trigger_error($this->error, E_USER_ERROR);
		}

		return new db_postgresql_query_result_lib($this->conn, $result);

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

		$query_string .= " FROM " . $table_name . ' ';

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

		$query_string .= " FROM " . $table_name . ' ';

		if(!empty($where_data)) {

			$query_string .= " WHERE ";

			foreach($where_data as $field => $value) {
				$query_string .= $field . " = '" . pg_escape_string($this->conn, $value) . "' and ";
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

		return pg_escape_string($this->conn, $value);

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

		$this->query("BEGIN");

		return true;

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

		$this->query("COMMIT");

		return true;

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

		$this->query("ROLLBACK");

		return true;

	} // End func rollback_trans

} // End of class db_postgresql_lib

// End of file
?>
