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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.1.0
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
      * Cache headers library object
      *
      * @var    object
      * @access protected
      * @since  1.1.0
      */
     protected $headers;

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

        // Define Cache headers library object
        $this->headers = new cache_headers_lib();

	} // End func __construct

	 /**
	  * Define item id
	  *
	  * @access protected
	  * @param  string $id
	  * @return string
	  * @since  3.0.0
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
	 * @since  1.0.0
	 */
    abstract public function stats();

    /**
     * Get configuration items
     *
     * @access public
     * @param  string $item
     * @return string
     * @since  1.1.0
     */
     public function config($item) {

         if(isset($this->config[$item])) {
             return $this->config[$item];
         }

         return false;

     }

     /**
      * Output Cache with valid headers
      *
      * @access public
      * @param  string $id
      * @param  array $replacements
      * @return bool
      * @since  1.1.0
      */
     public function output_content($id, $replacements = array()) {

         $cache_expiration = $this->config('cache_expiration');

         /* Define Headers max-age */
         if($cache_expiration == '-1') {
             $max_age = strtotime('+5 year');
         } elseif($cache_expiration == '0') {
             $max_age = strtotime('-1 day');
         } else {
             $max_age = strtotime('+' . $cache_expiration . ' minutes');
         }

         $content = $this->get_content($id);

         /* Cache expired. Initializing headers */
         if(!$content) {
             $last_modified = time();
             $this->output_headers($last_modified, $max_age);
             return false;
         }

         /* Replace possible values in cached content */
         if(!empty($replacements)) {
             foreach($replacements as $item => $value) {
                 $item = '{var.'.$item.'}';
                 $content = str_replace($item, $value, $content);
             }
         }

         /* Initializing headers */
         $last_modified = time() - $max_age;
         $this->output_headers($last_modified, $max_age);

         /* Outputting content end exiting */
         echo $content;

         return true;

     } // End func output_content

     /**
      * Output Cache with valid headers and exit.
      *
      * @access public
      * @param  string $id
      * @param  array $replacements
      * @return bool
      * @since  1.1.0
      */
     public function force_output_content($id, $replacements = array()) {

         if($this->output_content($id, $replacements)) {
             exit();
         }

         return false;

     } // End func force_output_content

     /**
      * Output headers
      *
      * @access public
      * @param  int $last_modified
      * @param  int $max_age
      * @return void
      * @since  1.1.0
      */
     public function output_headers($last_modified, $max_age) {

         $this->headers->output($last_modified, $max_age);

     } // End func output_headers

} /* End class cache_base_lib */