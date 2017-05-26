<?php
/**
 * toKernel - Universal PHP Framework.
 * toKernel error handler, error exception class for CLI mode.
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
 * @version    1.2.0
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
	 * Show error on command line
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
	protected static function show_error($err_code, $err_message, $file = NULL,
	                                     $line = NULL, $trace = NULL) {
		
		$err_type = self::get_error_type_text($err_code);
		
		$err_show_str = ' ' . $err_message;
		
		if(isset($file) and self::$config['app_mode'] == 'development') {
			$err_show_str .= TK_NL . ' File: ' . $file;
		}
		
		if(isset($line) and self::$config['app_mode'] == 'development') {
			$err_show_str .= TK_NL . ' Line: ' . $line;
		}
		
		if(self::$config['app_mode'] == 'production') {
			
			$err_type = self::$config['err_subject_production'];
			$err_show_str = self::$config['err_message_production'];
			
			/* In the production mode, the trace will not be displayed */
			$trace = NULL;
		} elseif(is_array($trace)) {
			
			$trace = array_reverse($trace);
			
			$err_show_str .= TK_NL;
			$err_show_str .= '[Debug trace]';
			$err_show_str .= TK_NL;
			
			foreach($trace as $i => $t) {
				
				if($t['function'] == 'trigger_error') {
					break;
				}
				
				if(!isset($t['class'])) {
					$t['class'] = '';
				}
				
				if(!isset($t['type'])) {
					$t['type'] = '';
				}
				
				if(!isset($t['file'])) {
					$t['file'] = '';
				}
				
				if(!isset($t['line'])) {
					$t['line'] = '';
				}
				
				$err_show_str .= sprintf("#%d %s%s%s() called at %s:%d \n", $i,$t['class'], $t['type'],$t['function'],$t['file'],$t['line']) . TK_NL;
			}
		}
		
		/* Show colored text message */
		if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
			
			$colored_string = TK_NL;
			$colored_string .= " \033[1;37m";
			$colored_string .= "\033[41m";
			$colored_string .= ' ' . $err_type . ' ' . "\033[0m" . TK_NL . TK_NL;
			
			$colored_string .= "\033[1;37m";
			$colored_string .= $err_show_str . "\033[0m" . TK_NL . TK_NL;
			
			$colored_string .= "\033[0;33m";
			$colored_string .= " toKernel - Universal PHP Framework. v" .
				TK_VERSION . TK_NL;
			
			$colored_string .= " http://www.tokernel.com" . "\033[0m" .
				TK_NL . TK_NL;
			
			fwrite(STDERR, $colored_string);
			
		} else {
			
			$string = TK_NL;
			$string .= ' ' . $err_type . TK_NL;
			$string .= $err_show_str . TK_NL . TK_NL;
			$string .= " toKernel - Universal PHP Framework. v" .
				TK_VERSION . TK_NL;
			
			$string .= " http://www.tokernel.com" . TK_NL . TK_NL;
			
			echo $string;
		}
		
		self::$error_displayed = true;
		
		self::log_debug('', ':============= HALTED WITH ERROR ! =============');
		
		exit(1);
		
	} // end func show_error
	
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
						
		if(!self::$config['show_notices'] and $error_group == 'notice') {
			return false;
		}
		
		if(!self::$config['show_warnings'] and $error_group == 'warning') {
			return false;
		}
		
		if(!self::$config['show_errors'] and $error_group == 'error') {
			return false;
		}
		
		if(!self::$config['show_unknown_errors'] and $error_group == 'unknown') {
			return false;
		}
				
		$trace = debug_backtrace(false);
		self::show_error($err_code, $err_message, $file, $line, $trace);
		
		return true;
		
	} // end func error
	
	/**
	 * @todo finish this
	 */
	public static function exception_error_handler($severity, $message, $file, $line) {
	
		self::$error_displayed = true;
		$error_group = self::get_error_group($severity);
		self::log($message, $severity, $file, $line);
		throw new ErrorException($message, 0, $severity, $file, $line);
		return true;
	}
	
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
