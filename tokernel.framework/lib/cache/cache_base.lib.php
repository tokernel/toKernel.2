<?php
/**
 * toKernel - Universal PHP Framework.
 * Base library file for caching.
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
 * cache_base_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
 abstract class cache_base_lib {

	 /**
	  * Library object for working with
	  * libraries in this class
	  *
	  * @var object
	  * @access protected
	  */
	 protected $lib;

	 /**
	  * Main Application object for
	  * accessing app functions from this class
	  *
	  * @var object
	  * @access protected
	  */
	 protected $app;

	/**
	 * Default configuration
	 *
	 * @access protected
	 * @var array
	 */
	protected $config = array(
		'cache_lib' => 'filecache',
		'cache_expiration' => 0,
		'cache_path' => '',
		'cache_file_extension' => 'cache',
		'cache_key_prefix' => 'abc123',
		'memcache_host' => 'localhost',
		'memcache_port' => '11211'
	);

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct(array $config) {

		$this->lib = lib::instance();
		$this->app = app::instance();

		// Set configuration
		$this->config = array_merge($this->config, $config);

	} // End func __construct

	 /**
	  * Define item id
	  *
	  * @access protected
	  * @param string $id
	  * @return string
	  * @since 3.0.0
	  */
	 protected function to_id($id) {
		 return md5($this->config['cache_key_prefix'] . $id);
	 }

	/**
	 * Return cache file expiration status.
	 * Expiration time defined in file: application/config/caching.ini
	 * section: [{given section]
	 *
	 * @abstract
	 * @access public
	 * @param string $id
	 * @param integer $minutes
	 * @return bool
	 */
	abstract public function expired($id, $minutes = NULL);

	/**
	 * Return cached content if exists or not expired.
	 * Else, return false;
	 *
	 * @abstract
	 * @access public
	 * @param string $id
	 * @param integer $minutes
	 * @return mixed string | bool
	 */
	abstract public function get_content($id, $minutes = NULL);

	/**
	 * Write cache content.
	 *
	 * @abstract
	 * @access public
	 * @param string $id
	 * @param string $buffer
	 * @param integer $minutes
	 * @return bool
	 */
    abstract public function write_content($id, $buffer, $minutes = NULL);

	/**
	 * Remove cache item by id
	 *
	 * @abstract
	 * @access public
	 * @param string $id
	 * @return bool
	 */
    abstract public function remove($id);

	/**
	 * Clean all cache
	 *
	 * @abstract
	 * @access public
	 * @return mixed integer | bool
	 */
    abstract public function clean_all();

	/**
	 * Get cache statistics
	 *
	 * @abstract
	 * @access public
	 * @return array
	 * @since 1.0.0
	 */
    abstract public function stats();

} // End class cache_base_lib

// End of file
?>