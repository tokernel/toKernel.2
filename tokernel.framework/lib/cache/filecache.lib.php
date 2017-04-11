<?php
/**
 * toKernel - Universal PHP Framework.
 * Library for caching on filesystem.
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
 * @version    3.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * filecache_lib class library.
 *
 * @author David A. <tokernel@gmail.com>
 */
class filecache_lib extends cache_base_lib {
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public function __construct($config) {
		
		parent::__construct($config);
		
		if(!isset($this->config['cache_path'])) {
			$this->config['cache_path'] = TK_APP_PATH . 'cache' . TK_DS;
		} elseif($this->config['cache_path'] == '') {
			$this->config['cache_path'] = TK_APP_PATH . 'cache' . TK_DS;
		}
		
		$this->config['cache_path'] = $this->lib->file->to_path($this->config['cache_path']);
		
	} // end func __construct
	
	/**
	 * Return cache file expiration status.
	 * Expiration time defined in application configuration
	 *
	 * @access public
	 * @param string $file_id
	 * @param integer $minutes
	 * @return bool
	 */
	public function expired($file_id, $minutes = NULL) {
		
		/* Set cache file path/name with extension */
		$file = $this->filename($file_id);
		
		if(!is_file($file)) {
			return true;
		}
		
		/*
		 * if minutes is not set, then set
		 * minutes from app configuration
		 */
		if(is_null($minutes)) {
			$minutes = $this->config['cache_expiration'];
		}
		
		/* -1 assume that the cache never expire */
		if($minutes == '-1') {
			return false;
		}
		
		/* Set seconds */
		$exp_sec = $minutes * 60;
		
		/* Get file time */
		$file_time = filemtime($file);
		
		/* Return true if cache expired */
		if(time() > ($exp_sec + $file_time)) {
			$this->remove($file_id);
			return true;
		} else {
			return false;
		}
		
	} // end func expired
	
	/**
	 * Return cached file content if exist.
	 * Return false if cache is expired.
	 *
	 * @access public
	 * @param string $file_id
	 * @param integer $minutes
	 * @return mixed string | bool
	 */
	public function get_content($file_id, $minutes = NULL) {
		
		/* Return false if expired */
		if($this->expired($file_id, $minutes) === true) {
			return false;
		}
		
		/* Set cache file path/name with extension */
		$file = $this->filename($file_id);
		
		/* Return false if file is not readable */
		if(!is_readable($file)) {
			return false;
		}
		
		return $this->lib->file->read($file);
		
	} // end func get_content
	
	/**
	 * Write cache content.
	 *
	 * @access public
	 * @param string $file_id
	 * @param string $buffer
	 * @param integer $minutes
	 * @return bool
	 */
	public function write_content($file_id, $buffer, $minutes = NULL) {
		
		/* If caching disabled, than return false */
		if($this->config['cache_expiration'] == 0) {
			return false;
		}
		
		/* Set cache file path/name with extension */
		$file = $this->filename($file_id);
		
		if($this->lib->file->write($file, $buffer)) {
			return true;
		}
		
		trigger_error('Can not write cache content: '. $file . ' (ID: ' . $file_id . ')', E_USER_WARNING);
		
		return false;
		
	} // end func write_content
	
	/**
	 * Remove cache file.
	 *
	 * @access public
	 * @param string $file_id
	 * @return bool
	 */
	public function remove($file_id) {
		
		/* Set cache file path/name with extension */
		$file = $this->filename($file_id);
		
		if(is_writable($file)) {
			unlink($file);
			return true;
		} else {
			return false;
		}
		
	} // end func remove
	
	/**
	 * Clean all cache files and deleted files count
	 *
	 * @access public
	 * @return int
	 */
	public function clean_all() {
		
		$del_files_count = 0;
		
		$cache_path = $this->config['cache_path'];
		$files = $this->lib->file->ls($cache_path, '-', false, $this->config['cache_file_extension']);
		
		if(!$files) {
			return $del_files_count;
		}
		
		foreach($files as $file) {
			if(unlink($cache_path . $file)) {
				$del_files_count++;
			}
		}
		
		return $del_files_count;
		
	} // end func clean_all
	
	/**
	 * Make cache file name with path and extendion.
	 *
	 * @access public
	 * @param string $file_id
	 * @return mixed string | bool
	 */
	protected function filename($file_id) {
		
		if(trim($file_id) == '') {
			return false;
		}
		
		$file_name = $this->to_id($file_id) . '.' . $this->config['cache_file_extension'];
		
		return $this->config['cache_path'] . $file_name;
		
	} // end func filename
	
	/**
	 * Get cache files statistics
	 *
	 * @access public
	 * @return mixed array | bool
	 * @since 2.1.0
	 */
	public function stats() {
		
		$files_count = 0;
		$total_size = 0;
		
		$cache_path = $this->config['cache_path'];
		$files = $this->lib->file->ls($cache_path, '-', false, $this->config['cache_file_extension']);
		
		if(!$files) {
			return false;
		}
		
		foreach($files as $file) {
			
			$files_count++;
			$total_size += filesize($cache_path . $file);
			
		}
		
		return array(
			'files_count' => $files_count,
			'bytes' => $total_size
		);
		
	} // End func stats
	
} /* End of class filecache_lib */