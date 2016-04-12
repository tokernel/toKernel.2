<?php
/**
 * toKernel - Universal PHP Framework.
 * Memcache - library for caching with memcache.
 * System/Memcached and PHP Memcache extension required.
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
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * memcache_lib class library.
 *  
 * @author David A. <tokernel@gmail.com>
 */
 class memcache_lib extends cache_base_lib {

/**
 * Memcache object
 * 
 * @access protected
 * @var object
 */ 
 protected $memcache = NULL;
 
/**
 * Class constructor
 * 
 * @access public
 * @param array $config
 * @return void
 */ 
 public function __construct($config = array()) {

	parent::__construct($config);
    
	$this->connect();
    
 } // end func __construct 

/**
 * Connect to memcache server
 * 
 * @access protected
 * @return bool
 */ 
 protected function connect() {
	 
	 if(!is_null($this->memcache)) {
		 return true;
	 }
	 
	 $this->memcache = new Memcache;
	 
	 if(!$this->memcache->connect($this->config['memcache_host'], $this->config['memcache_port'])) {
		 trigger_error('Could not connect to memcache server.', E_USER_ERROR);
	 }

	 return true;
	 
 } // End func connect
	
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

	// Define cache item key
	 $id = $this->to_id($id);

	// $minutes variable never used.
	// It is just a patern of abstract method

	$content = $this->get_content($id); 
	
	if(!$content) {
		return false;
	} else {
		return true;
	}
 	
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

	// Define cache item key
	 $id = $this->to_id($id);

	// $minutes variable never used.
	// It is just a patern of abstract method

	$content = $this->memcache->get($id);
	
	if(!$content) {
		return false;
	}
	 
 	return $content;
	
 } // end func get_content
 
/**
 * Write cache content.
 * 
 * @access public 
 * @param string $id
 * @param string $buffer
 * @param mixed integer | NULL $minutes
 * @return bool
 */ 
 public function write_content($id, $buffer, $minutes = NULL) {

	// Define cache item key
	$id = $this->to_id($id);

 	if(is_null($minutes)) {
		$minutes = $this->config['cache_expiration'];
	}
	
	if($minutes == '-1') {
		$minutes = 0;
	} else {
		$minutes = $minutes * 60;
	}

 	return $this->memcache->set($id, $buffer, false, $minutes);
	 
} // end func write_content

/**
 * Remove cache by id.
 * 
 * @access public
 * @param string $id
 * @return bool
 */ 
 public function remove($id) {

	// Define cache item key
	$id = $this->to_id($id);

 	return $this->memcache->delete($id);
	
 } // end func remove
 
/**
 * Clean all cache
 * 
 * @access public
 * @return bool
 */ 
 public function clean_all() {
 	return $this->memcache->flush();
 } // end func clean_all
 
/**
 * Get cache statistics 
 * 
 * @access public
 * @return array
 * @since 1.1.0
 */ 
 public function stats() {
	 return $this->memcache->getStats();
 } // End func stats

/* End of class memcache_lib */
}

/* End of file */
?>