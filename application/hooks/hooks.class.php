<?php
/**
 * toKernel - Universal PHP Framework.
 * Hooks class library
 * In fact, it is not possible to output any data in hooks,
 * such as echo 'some data'; in HTTP mode.
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
 * @category   hooks
 * @package    application
 * @subpackage hooks
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.3.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * hooks class
 *
 * @author David A. <tokernel@gmail.com>
 */

class hooks extends hooks_base {
	
	/**
	 * Instances: app, lib and addons accessible inside this class.
	 *
	 * $this->app->...
	 * $this->lib->...
	 * $this->addons...
	 *
	 */
	
	/**
	 * hooks class constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
	}
		
	/**
	 * Hook before run main callable addon in HTTP and CLI modes
	 *
	 * @access public
	 * @return void
	 */
	public function before_run() {
		
		// Your code here...
		
	} // end func before_run
	
	/**
	 * Hook after run main callable addon in HTTP and CLI modes
	 *
	 * @access public
	 * @return void
	 */
	public function after_run() {
		
		// Your code here...
		
	} // end func after_run
	
	/**
	 * Hook before run main callable addon in CLI mode
	 *
	 * @access public
	 * @return void
	 */
	public function cli_before_run() {
		
		// Your code here...
		
	} // end func cli_before_run
	
	/**
	 * Hook after run main callable addon in CLI mode
	 *
	 * @access public
	 * @return void
	 */
	public function cli_after_run() {
		
		// Your code here...
		
	} // end func cli_after_run
	
	/**
	 * Hook before run main callable addon in HTTP mode
	 *
	 * @access public
	 * @return void
	 */
	public function http_before_run() {
		
		// Your code here...
		
	} // end func
	
	/**
	 * Hook after run main callable addon in HTTP mode
	 *
	 * @access public
	 * @return void
	 */
	public function http_after_run() {
		
		// Your code here...
		
	} // end func http_after_run
	
	/**
	 * Hook before output in HTTP mode
	 * NOTICE: The argument $output_buffer is the main application output buffer content,
	 * however all changes on it will be on your own risk.
	 *
	 * @access public
	 * @param string $output_buffer
	 * @return string
	 */
	public function http_before_output($output_buffer) {
		
		// Your code here...
		// NOTICE: You can do with buffer what you want but with your own risk!
		// Listening the music of Sandra now ... ;)
		
		return $output_buffer;
		
	} // end func http_before_output
	
	/**
	 * Hook after output in HTTP mode
	 *
	 * @access public
	 * @param string $output_buffer
	 * @return void
	 */
	public function http_after_output($output_buffer) {
		
		// Your code here...
		
	} // end func http_after_output
	
} // End class hooks