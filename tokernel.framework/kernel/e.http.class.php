<?php
/**
 * toKernel - Universal PHP Framework.
 * toKernel error handler, error exception class for HTTP mode.
 * Child of tk_e_core class.
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
 * @category   kernel
 * @package    framework
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * tk_e class
 *
 * @author David A. <tokernel@gmail.com>
 */
class tk_e extends tk_e_core {
	
	/**
	 * Show error
	 *
	 * @static
	 * @access protected
	 * @param integer $err_code
	 * @param string $err_message
	 * @param string $file
	 * @param integer $line
	 * @param mixed $trace
	 * @return void
	 */
	protected static function show_error($err_code, $err_message, $file = NULL, $line = NULL, $trace = NULL) {
		
		$error_group = self::get_error_group($err_code);
		$err_type = self::get_error_type_text($err_code);
		
		$err_show_str = $err_message;
		
		if(isset($file) and self::$config['app_mode'] == 'development') {
			$err_show_str .= TK_NL . 'File: ' . $file;
		}
		
		if(isset($line) and self::$config['app_mode'] == 'development') {
			$err_show_str .= TK_NL . 'Line: ' . $line;
		}
		
		if(self::$config['app_mode'] == 'production') {
			
			$err_type = self::$config['err_subject_production'];
			$err_show_str = self::$config['err_message_production'];
			
			/* In the production mode, the trace will not be displayed */
			$trace = NULL;
			
		}
				
		// Clean all buffer levels.
		while(ob_get_level()) {
			ob_end_clean();
		}
						
		if(!headers_sent()) {
			header('HTTP/1.1 500 Internal Server Error', true, 500);
		}
		
		$err_tpl_file = self::get_error_template_file($error_group);
		
		if(is_array($trace)) {
			$trace = array_reverse($trace);
		}
		
		if($err_tpl_file != false) {
			require($err_tpl_file);
		} else {
			
			echo TK_NL;
			echo '<strong>';
			echo $err_type . TK_NL;
			echo '</strong>';
			echo $err_show_str . TK_NL;
			echo TK_NL;
			
			if(!empty($trace)) {
				echo '<pre>';
				print_r($trace);
				echo '</pre>';
			}
			
		}
		
		self::$error_displayed = true;
		
		self::log_debug('', ':============= HALTED WITH ERROR ! =============');
		
		exit(1);
		
	} // end func show_error
		
	/**
	 * Get error template file
	 *
	 * @access protected
	 * @param string $err_group
	 * @return mixed
	 */
	protected static function get_error_template_file($err_group) {
		
		switch($err_group) {
			
			case 'error':
				$error_file = 'error.tpl.php';
				break;
			default:
				$error_file = 'warning.tpl.php';
				break;
		}
		
		$mode = self::$lib->url->mode();
		
		if(defined('TK_APP_PATH')) {
			$app_error_file = TK_APP_PATH . 'templates' . TK_DS . $mode . TK_DS . $error_file;
		} else {
			$app_error_file = '';
		}
		
		if(is_file($app_error_file)) {
			return $app_error_file;
		} else {
			return false;
		}
		
	} // end func get_error_template_file
	
	
	/**
	 * Error handler
	 *
	 * @access public
	 * @param integer $err_code
	 * @param string $err_message
	 * @param string $file
	 * @param integer $line
	 * @return bool
	 */
	public static function error($err_code, $err_message, $file = NULL, $line = NULL) {
		
		$error_group = self::get_error_group($err_code);
		self::log($err_message, $err_code, $file, $line);
		
		$trace = debug_backtrace(false);
		
		if(self::$config['show_notices'] == '1' and $error_group == 'notice') {
			self::show_error($err_code, $err_message, $file, $line, $trace);
		}
		
		if(self::$config['show_warnings'] == '1' and $error_group == 'warning') {
			self::show_error($err_code, $err_message, $file, $line, $trace);
		}
		
		if(self::$config['show_errors'] == '1' and $error_group == 'error') {
			self::show_error($err_code, $err_message, $file, $line, $trace);
		}
		
		if(self::$config['show_unknown_errors'] == '1' and $error_group == 'unknown') {
			self::show_error($err_code, $err_message, $file, $line, $trace);
		}
		
		return true;
		
	} // end func error
	
	/**
	 * Shutdown handler
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function shutdown() {
		
		$error = error_get_last();
		
		if($error !== NULL and self::$error_displayed === true) {
			self::log($error['message'], $error['type'], $error['file'], $error['line']);
			exit(1);
		}
		
		if($error !== NULL and self::$error_displayed === false) {
			self::error($error['type'], $error['message'], $error['file'], $error['line']);
			exit(1);
		}
		
	} // end func shutdown
	
} /* End of class tk_e */