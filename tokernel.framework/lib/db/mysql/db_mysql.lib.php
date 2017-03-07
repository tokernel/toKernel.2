<?php
/**
 * toKernel - Universal PHP Framework.
 * MySQL database class library uses PHP MySQLi (MySQL Improved) extension
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
 * db_mysql_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class db_mysql_lib extends db_base_lib {

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

	$this->debug_log(__CLASS__ . '->' . __FUNCTION__ . '() Adapter MySQL initialized.');
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
	if($this->conn and mysqli_ping($this->conn) === true) {
		return true;
	}

	// Check if mysqli extension exists
	if(!function_exists('mysqli_connect')) {
		$this->error = "The MySQLi extension is not available.";
		trigger_error($this->error, E_USER_ERROR);
		return false;
	}

	// Set port if defined
	if ($this->config['port'] != '') {
		 $this->config['host'] .= ':' . $this->config['port'];
	}

	// Connect to server
	$this->conn = @mysqli_connect(
		$this->config['host'],
		$this->config['username'],
		$this->config['password']
	);

	 if(!$this->conn) {
		 $this->error = mysqli_connect_error();
		 trigger_error($this->error, E_USER_ERROR);
	 }

	 // Set charset
	 if($this->config['charset'] != '') {
	    if (!mysqli_set_charset($this->conn, $this->config['charset'])) {
		    $this->error = mysqli_error($this->conn);
		    trigger_error($this->error, E_USER_ERROR);
	    }
	 }

	 $this->debug_log('Connected to MySQL server: ' . $this->config['host'] . ' successfully.');

	 // Select database
	 if(!mysqli_select_db($this->conn, $this->config['database'])) {
		 $this->error = mysqli_error($this->conn);
		 trigger_error($this->error, E_USER_ERROR);
	 }

	 $this->debug_log('Selected MySQL Database: ' . $this->config['database'] . ' successfully.');

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
		mysqli_close($this->conn);

		$this->debug_log('Closed MySQL Server connection from: ' . $this->config['host']);
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
			$query_string .= "'" . mysqli_real_escape_string($this->conn, $value) . "', ";
		}

		$query_string = rtrim($query_string, ', ');
		$query_string .= '), ';
	}

	$query_string = rtrim($query_string, ', ');

	$result = mysqli_query($this->conn, $query_string);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

	$affected_rows = mysqli_affected_rows($this->conn);

	$this->debug_log('MySQL insert_batch() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

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
		 $query_string .= "'" . mysqli_real_escape_string($this->conn, $value) . "', ";
	 }

	 $query_string = rtrim($query_string, ', ');

	 $query_string .= ')';

	 $result = mysqli_query($this->conn, $query_string);

	 if(!$result) {
		 $this->error = mysqli_error($this->conn);
		 trigger_error($this->error, E_USER_ERROR);
	 }

	 $insert_id = mysqli_insert_id($this->conn);

	 $this->debug_log('MySQL insert() success : ' . $query_string . ' / insert id: ' . $insert_id);

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

	return mysqli_insert_id($this->conn);

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
		$query_string .= $field . " = '" . mysqli_real_escape_string($this->conn, $value) . "', ";
	}

	$query_string = rtrim($query_string, ', ');

	$query_string .= ' WHERE ';

	foreach($where as $field => $value) {
		$query_string .= $field . " = '" . mysqli_real_escape_string($this->conn, $value) . "' and ";
	}

	$query_string = rtrim($query_string, 'and ');

	$result = mysqli_query($this->conn, $query_string);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

	$affected_rows = mysqli_affected_rows($this->conn);

	$this->debug_log('MySQL update() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

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
		$query_string .= $field . " = '" . mysqli_real_escape_string($this->conn, $value) . "' and ";
	}

	$query_string = rtrim($query_string, 'and ');

	$result = mysqli_query($this->conn, $query_string);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

	$affected_rows = mysqli_affected_rows($this->conn);

	$this->debug_log('MySQL delete() success : ' . $query_string . ' / Affected rows: ' . $affected_rows);

	return $affected_rows;

 } // End func delete

/**
 * Run MySQL Query
 *
 * This method will return MySQL Query result object if it's selects records
 * See: db_mysql_query_result class
 *
 * @access public
 * @param string $query_string
 * @param mixed array | null $values_array
 * @return object db_mysql_query_result_lib
 */
 public function query($query_string, array $values_array = NULL) {

	// Check connection
	$this->connect();

	// Bind query values from array
	if(is_array($values_array) and !empty($values_array)) {

		foreach($values_array as $value) {

			$pos = strpos($query_string, '?');
			if ($pos !== false) {
				$replace = "'" . mysqli_real_escape_string($this->conn, $value) . "'";
				$query_string = substr_replace($query_string, $replace, $pos, 1);
			}

		}

	}

	$result = mysqli_query($this->conn, $query_string);

	if(!$result) {
		 $this->error = mysqli_error($this->conn);
		 trigger_error($this->error, E_USER_ERROR);
	}

	return new db_mysql_query_result_lib($this->conn, $result);

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
			$query_string .= $field . " = '" . mysqli_real_escape_string($this->conn, $value) . "' and ";
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

	return mysqli_real_escape_string($this->conn, $value);

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

	if(!function_exists('mysqli_begin_transaction')) {
		return mysqli_autocommit($this->conn, $auto_commit);
	}

	mysqli_autocommit($this->conn, false);

	$result = mysqli_begin_transaction($this->conn);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

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

	$result = mysqli_commit($this->conn);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

	mysqli_autocommit($this->conn, true);

	return $result;

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

	$result = mysqli_rollback($this->conn);

	if(!$result) {
		$this->error = mysqli_error($this->conn);
		trigger_error($this->error, E_USER_ERROR);
	}

	return true;

 } // End func rollback_trans

} // End of class db_mysql_lib

// End of file
?>
