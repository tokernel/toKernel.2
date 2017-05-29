<?php
/**
 * toKernel - Universal PHP Framework.
 * Main response class for CLI (Command line interface) mode.
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.3.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * response class
 *
 * @author David A. <tokernel@gmail.com>
 */
class response {
	
	/**
	 * Status of this class instance
	 *
	 * @staticvar object
	 * @access private
	 */
	private static $instance;
	
	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access private
	 */
	private $lib;
		
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
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
	 */
	final private function __construct() {
		
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
		
	}
	
	/**
	 * Prevent cloning of the object.
	 * Trigger E_USER_ERROR if attempting to clone.
	 *
	 * @throws ErrorException
	 * @access public
	 * @return void
	 */
	public function __clone() {
		throw new ErrorException('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
	}
	
	/**
	 * Singleton method used to access the object
	 *
	 * @static
	 * @final
	 * @access public
	 * @return object $instance
	 */
	final public static function instance() {
		
		if(!isset(self::$instance)) {
			$obj = __CLASS__;
			self::$instance = new $obj;
		}
		
		return self::$instance;
		
	} // end func instance
	
	/**
	 * Set status code
	 * In the command line the status can be 0 or 1.
	 *
	 * @access public
	 * @param int $code
	 * @return void
	 */
	public function set_status($code) {
		$this->status = $code;
	} // End func set_status
	
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
	public function output($string, $fore_color = NULL, $back_color = NULL, $new_line = true) {
		
		/* Output string to screen without colors */
		if(!$this->colored_output) {
			/* Output to screen */
			
			if($new_line == true) {
				$string .= TK_NL;
			}
			
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
		
		if($new_line == true) {
			$colored_string .= TK_NL;
		}
		
		/* Output to screen */
		fwrite(STDOUT, $colored_string);
		
	}
		
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
		
		$this->output($message, 'green');
		
		/* Output message if not empty */
		if(!empty($show_message)) {
			/* Make colored text */
			$this->output(' ', null, null, false);
			$this->output(' '.$show_message, 'white', 'red', false);
			$this->output(' ', null, null, true);
		}
		
		/* Output usage info */
		$message = TK_NL;
		$message .= " Usage: /usr/bin/php " . TK_ROOT_PATH . 'index.php';
		
		$message .= " {addon_name}";
		$message .= " {action_name}";
		
		$message .= " [argument_1] [argument_N]";
		$message .= TK_NL;
		
		$this->output($message, 'white');
		
	} // end func output_usage
	
	/**
	 * Print colors on screen
	 *
	 * @access public
	 * @return void
	 */
	public function output_colors() {
		
		if(empty($this->fore_colors)) {
			$this->output(TK_NL);
			$this->output("Unable to output colors. This Operating system doesn't support this feature.");
			$this->output(TK_NL);
		}
		
		$this->output(TK_NL);
		$this->output(' [Forecolors] ' . "\t" . '[Backcolors]', 'black', 'yellow', false);
		$this->output(TK_NL);
		
		reset($this->fore_colors);
		reset($this->back_colors);
		
		ksort($this->fore_colors);
		ksort($this->back_colors);
		
		for($i = 0; $i < count($this->fore_colors); $i++) {
			$this->output(' '. key($this->fore_colors) . ' ', key($this->fore_colors), NULL, false);
			
			if(key($this->back_colors)) {
				$j = '';
				$val = 15 - strlen(key($this->fore_colors));
				for($k = 0; $k < $val; $k++) {
					$j .= ' ';
				}
				$this->output(' ' . $j, NULL, NULL, false);
				$this->output(' ' . key($this->back_colors) . ' ', 'black', key($this->back_colors), false);
			}
			
			$this->output(TK_NL);
			next($this->fore_colors);
			next($this->back_colors);
		}
		
	} // end func output_colors
	
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
	
} /* End class response */