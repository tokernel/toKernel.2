<?php
/**
 * toKernel - Universal PHP Framework.
 * CLI - Command line interface class library.
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
 * @version    2.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @deprecated Instead of this library you have to use $this->response->... $this->request->...
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * cli_lib class library
 *
 * @author David A. <tokernel@gmail.com>
 */
class cli_lib {
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;
	
	/**
	 * Status of this class initialization
	 *
	 * @access protected
	 * @staticvar bool
	 */
	protected static $initialized = false;
	
	/**
	 * Is colored output enabled.
	 * Detect this option by OS.
	 *
	 * @access protected
	 * @var bool
	 */
	protected $colored_output = false;
	
	/**
	 * Foreground colors for CLI output
	 *
	 * @access protected
	 * @var array
	 */
	protected $fore_colors = array();
	
	/**
	 * Background colors for CLI output
	 *
	 * @access protected
	 * @var array
	 */
	protected $back_colors = array();
	
	/**
	 * Language prefix
	 *
	 * @access protected
	 * @var string
	 */
	protected $language_prefix = 'en';
	
	/**
	 * CLI parameters
	 *
	 * @access protected
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * CLI requested addon
	 *
	 * @access protected
	 * @var string
	 */
	protected $addon;
	
	/**
	 * CLI requested action
	 *
	 * @access protected
	 * @var string
	 */
	protected $action;
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function  __construct() {
		
		$this->lib = lib::instance();
		
		/* Detect OS for colored output */
		if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
			
			$this->colored_output = true;
			
			/* Set CLI text output Colors */
			$this->fore_colors['black'] = '0;30';
			$this->fore_colors['dark_gray'] = '1;30';
			$this->fore_colors['blue'] = '0;34';
			$this->fore_colors['light_blue'] = '1;34';
			$this->fore_colors['green'] = '0;32';
			$this->fore_colors['light_green'] = '1;32';
			$this->fore_colors['cyan'] = '0;36';
			$this->fore_colors['light_cyan'] = '1;36';
			$this->fore_colors['red'] = '0;31';
			$this->fore_colors['light_red'] = '1;31';
			$this->fore_colors['purple'] = '0;35';
			$this->fore_colors['light_purple'] = '1;35';
			$this->fore_colors['brown'] = '0;33';
			$this->fore_colors['yellow'] = '1;33';
			$this->fore_colors['light_gray'] = '0;37';
			$this->fore_colors['white'] = '1;37';
			
			$this->back_colors['black'] = '40';
			$this->back_colors['red'] = '41';
			$this->back_colors['green'] = '42';
			$this->back_colors['yellow'] = '43';
			$this->back_colors['blue'] = '44';
			$this->back_colors['magenta'] = '45';
			$this->back_colors['cyan'] = '46';
			$this->back_colors['light_gray'] = '47';
			
		} // end if colored output enabled
		
	} // end constructor
	
	/**
	 * Clean argument
	 * Remove first "--" chars if exists.
	 *
	 * @access protected
	 * @param mixed
	 * @return string
	 */
	protected function clean_arg($arg) {
		
		if(substr($arg, 0, 2) == '--') {
			$arg = substr($arg, 2);
		}
		
		return $arg;
		
	} // End func clean_arg
	
	/**
	 * Initialize CLI
	 * This function will called from application instance at once.
	 *
	 * @access public
	 * @param array $args
	 * @return bool
	 */
	public function init($args) {
		
		/* Return true if already initialized */
		if(self::$initialized == true) {
			
			trigger_error(
				'CLI initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!',
				E_USER_WARNING
			);
			
			return true;
		}
		
		tk_e::log_debug('Start with arguments "' . implode(' ' ,$args) . '"', __CLASS__.'->'.__FUNCTION__);
		
		/*
		* Remove first element of $args array,
		* that will be the file name: index.php.
		*/
		array_shift($args);
		
		/* Check arguments count */
		if(empty($args)) {
			
			tk_e::log_debug('Exit! Invalid Command line arguments.', __CLASS__.'->'.__FUNCTION__);
			tk_e::log("Invalid Command line arguments!", E_USER_NOTICE, __FILE__, __LINE__);
			
			$this->output_usage("Invalid Command line arguments!");
			
			exit(TK_NO_ARGS);
		}
		
		/* Show usage on screen and exit, if called help action. */
		if(in_array($args[0], array('--help', '--usage', '-help', '-h', '-?'))) {
			
			tk_e::log_debug('Exit! Show usage/help.', __CLASS__.'->'.__FUNCTION__);
			
			$this->output_usage();
			exit(0);
		}
		
		tk_e::log_debug('Parsing arguments', __CLASS__.'->'.__FUNCTION__);
		
		/* Check, if routing exists for first argument */
		$args = routing::parse($args);
		
		// Set addon from Arguments
		$this->addon = $this->clean_arg($args[0]);
		array_shift($args);
		
		// Set action from arguments
		if(!empty($args)) {
			
			$this->action = $this->clean_arg($args[0]);
			array_shift($args);
			
			// Action is empty
		} else {
			
			$this->output_usage('Action is empty.');
			exit(TK_NO_ARGS);
		}
		
		/* Set params if not empty */
		if(!empty($args)) {
			foreach($args as $a) {
				$this->params[] = $this->clean_arg($a);
			}
		}
		
		
		self::$initialized = true;
		
		tk_e::log_debug('End with params - "' . implode(',', $this->params) . '"', __CLASS__.'->'.__FUNCTION__);
		
		return true;
		
	} // end func init
	
	/**
	 * Read data from command line
	 *
	 * @access public
	 * @return string
	 */
	public function in() {
		$handle = trim(fgets(STDIN));
		return $handle;
	} // end func in
	
	/**
	 * Output colored string to screen if $this->colored_output is true.
	 * Else, if OS is WIN, just output string without colors.
	 *
	 * @access public
	 * @param string $string
	 * @param string $fore_color = NULL
	 * @param string $back_color = NULL
	 * @param mixed $new_line = true
	 * @return void
	 */
	public function out($string, $fore_color = NULL, $back_color = NULL, $new_line = true) {
		
		if($new_line == true) {
			$string .= TK_NL;
		}
		
		/* Output string to screen without colors */
		if(!$this->colored_output) {
			/* Output to screen */
			fwrite(STDOUT, $string);
			return;
		}
		
		$colored_string = '';
		
		/* Check if given foreground color found */
		if(isset($this->fore_colors[$fore_color]) and !empty($fore_color)) {
			$colored_string .= "\033[".$this->fore_colors[$fore_color]."m";
		}
		
		/* Check if given background color found */
		if(isset($this->back_colors[$back_color]) and !empty($back_color)) {
			$colored_string .= "\033[" . $this->back_colors[$back_color] . "m";
		}
		
		/* Add string and end coloring */
		$colored_string .=  $string . "\033[0m";
		
		/* Output to screen */
		fwrite(STDOUT, $colored_string);
		
	} // end func out
	
	/**
	 * Return text with colored format.
	 * If the OS where is application running is not *nix like, the method will return value as is.
	 *
	 * @access public
	 * @param string $string
	 * @param string $fore_color
	 * @param string $back_color
	 * @return string
	 */
	public function get_ct($string, $fore_color = NULL, $back_color = NULL) {
		
		/* Return string without colors */
		if(!$this->colored_output) {
			return $string;
		}
		
		$colored_string = '';
		
		/* Check if given foreground color found */
		if(isset($this->fore_colors[$fore_color]) and !empty($fore_color)) {
			$colored_string .= "\033[".$this->fore_colors[$fore_color]."m";
		}
		
		/* Check if given background color found */
		if(isset($this->back_colors[$back_color]) and !empty($back_color)) {
			$colored_string .= "\033[" . $this->back_colors[$back_color] . "m";
		}
		
		/* Add string and end coloring */
		$colored_string .=  $string . "\033[0m";
		
		return $colored_string;
	} // end func get_ct
	
	/**
	 * Output CLI Usage on screen
	 *
	 * @access public
	 * @param string $show_message
	 * @return void
	 */
	public function output_usage($show_message = NULL) {
		
		/* Output copyright info */
		$message = '';
		
		$message .= TK_NL." -";
		$message .= TK_NL." | toKernel - Universal PHP Framework v".TK_VERSION;
		$message .= TK_NL." | Copyright (c) " . date('Y') . " toKernel <framework@tokernel.com>";
		$message .= TK_NL." | ";
		$message .= TK_NL." | Running in " . php_uname();
		$message .= TK_NL." - ";
		
		$this->out($message, 'green');
		
		/* Output message if not empty */
		if(!empty($show_message)) {
			/* Make colored text */
			$this->out(' ', null, null, false);
			$this->out(' '.$show_message, 'white', 'red', false);
			$this->out(' ', null, null, true);
		}
		
		/* Output usage info */
		$message = TK_NL;
		$message .= " Usage: /usr/bin/php " . TK_ROOT_PATH . 'index.php';
		
		$message .= " {addon_name}";
		$message .= " {action_name}";
		
		$message .= " [argument_1] [argument_2]";
		$message .= TK_NL;
		
		$this->out($message, 'white');
		
	} // end func output_usage
	
	/**
	 * Print colors on screen
	 *
	 * @access public
	 * @return void
	 */
	public function output_colors() {
		
		if(empty($this->fore_colors)) {
			$this->out(TK_NL);
			$this->out("Unable to output colors. This Operating system doesn't support this feature.");
			$this->out(TK_NL);
		}
		
		$this->out(TK_NL);
		$this->out(' [Forecolors] ' . "\t" . '[Backcolors]', 'black', 'yellow', false);
		$this->out(TK_NL);
		
		reset($this->fore_colors);
		reset($this->back_colors);
		
		ksort($this->fore_colors);
		ksort($this->back_colors);
		
		for($i = 0; $i < count($this->fore_colors); $i++) {
			$this->out(' '. key($this->fore_colors) . ' ', key($this->fore_colors), NULL, false);
			
			if(key($this->back_colors)) {
				$j = '';
				$val = 15 - strlen(key($this->fore_colors));
				for($k = 0; $k < $val; $k++) {
					$j .= ' ';
				}
				$this->out(' ' . $j, NULL, NULL, false);
				$this->out(' ' . key($this->back_colors) . ' ', 'black', key($this->back_colors), false);
			}
			
			$this->out(TK_NL);
			next($this->fore_colors);
			next($this->back_colors);
		}
		
	} // end func output_colors
	
	/**
	 * Return language prefix
	 *
	 * @access public
	 * @return string
	 */
	public function language_prefix() {
		return $this->language_prefix;
	}
	
	/**
	 * Return addon id
	 *
	 * @access public
	 * @return string
	 */
	public function addon() {
		return $this->addon;
	}
	
	/**
	 * Return action of addon
	 *
	 * @access public
	 * @return string
	 */
	public function action() {
		return $this->action;
	}
	
	/**
	 * Return parameter value by name or parameters array
	 *
	 * @access public
	 * @param string $item
	 * @return mixed array | string | bool
	 */
	public function params($item = NULL) {
		
		/* Return parameters array */
		if(is_null($item)) {
			return $this->params;
		}
		
		/* Return parameter value by name */
		if(isset($this->params[$item])) {
			return $this->params[$item];
		}
		
		/* Parameter not exists */
		return false;
		
	} // end func params
	
	/**
	 * Return parameters count
	 *
	 * @access public
	 * @return integer
	 */
	public function params_count() {
		return count($this->params);
	}
	
} /* End of class cli_lib */