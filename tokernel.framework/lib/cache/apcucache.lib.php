<?php
/**
 * toKernel - Universal PHP Framework.
 * Library for caching on Alternative PHP Cache.
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
 * apcucache_lib class library.
 *
 * @author David A. <tokernel@gmail.com>
 */
class apcucache_lib extends cache_base_lib {

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public function __construct($config = array()) {

		parent::__construct($config);

	} // end func __construct

	/**
	 * Return cache expiration status.
	 * Expiration time defined in application configuration
	 *
	 * @access public
	 * @param string $id
	 * @param mixed $minutes
	 * @return bool
	 */
	public function expired($id, $minutes = NULL) {

		$id = $this->to_id($id);

		if(!apcu_exists($id)) {
			return true;
		}

		return false;

	} // end func expired

	/**
	 * Return cached content if exists or not expired.
	 * Else, return false;
	 *
	 * @access public
	 * @param string $id
	 * @param mixed $minutes
	 * @return mixed string | bool
	 */
	public function get_content($id, $minutes = NULL) {

		$id = $this->to_id($id);

		return apcu_fetch($id);

	} // end func get_content

	/**
	 * Write cache content.
	 *
	 * @access public
	 * @param string $id
	 * @param mixed $buffer
	 * @param mixed integer | NULL $minutes
	 * @return bool
	 */
	public function write_content($id, $buffer, $minutes = NULL) {

		$id = $this->to_id($id);

		if(is_null($minutes)) {
			$minutes = $this->config['cache_expiration'];
		}

		if($minutes == '-1') {
			$minutes = 0;
		}

		$ttl = $minutes * 60;

		return apcu_store($id, $buffer, $ttl);

	} // end func write_content

	/**
	 * Remove cache by id.
	 *
	 * @access public
	 * @param string $id
	 * @return bool
	 */
	public function remove($id) {

		$id = $this->to_id($id);

		if(apcu_exists($id)) {
			apcu_delete($id);
			return true;
		}

		return false;

	} // end func remove

	/**
	 * Clean all cache
	 *
	 * @access public
	 * @return bool
	 */
	public function clean_all() {

		return apcu_clear_cache();

	} // end func clean_all

	/**
	 * Get cache statistics
	 *
	 * @access public
	 * @return array
	 * @since 1.1.0
	 */
	public function stats() {

		return apcu_cache_info();

	} // End func stats


} // End class apcucache_lib

// End of file
?>