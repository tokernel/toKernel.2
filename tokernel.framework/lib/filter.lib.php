<?php
/**
 * toKernel - Universal PHP Framework.
 * Data filtering class library.
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
 * @version    3.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * filter_lib class
 *
 * Data filtering class library.
 *
 * @author David A. <tokernel@gmail.com>
 */
class filter_lib {
	
	/**
	 * lib object to access all libraries
	 *
	 * @var object
	 * @access private
	 */
	private $lib;
		
	/**
	 * Removable expressions.
	 *
	 * @access private
	 * @var array
	 */
	private $expressions_to_replaced = array(
		'document.cookie'	=> '',
		'document.write'	=> '',
		'.parentNode'		=> '',
		'.innerHTML'		=> '',
		'window.location'	=> '',
		'self.location'     => '',
		'-moz-binding'		=> '',
		'<!--'				=> '&lt;!--',
		'-->'				=> '--&gt;',
		'<![CDATA['			=> '&lt;![CDATA['
	);
	
	/**
	 * Removable patterns.
	 *
	 * @access private
	 * @var array
	 */
	private $patterns_to_remove = array(
		"#javascript\s*:#i"				=> '',
		"#expression\s*(\(|&\#40;)#i"	=> '',
		"#vbscript\s*:#i"				=> '',
		"#Redirect\s+302#i"				=> ''
	);
	
	/**
	 * Removable invisible chars.
	 *
	 * @access private
	 * @var array
	 */
	private $chars_to_remove = array(
		'/%0[0-8bcef]/',	// url encoded 00-08, 11, 12, 14, 15
		'/%1[0-9a-f]/',		// url encoded 16-31
		'/[\x00-\x08]/',	// 00-08
		'/\x0b/', '/\x0c/',	// 11, 12
		'/[\x0e-\x1f]/'		// 14-31
	);
		
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->lib = lib::instance();
	} // end constructor
		
	/**
	 * Clean data.
	 * if xss_clean is true this function will call clean_xss function.
	 *
	 * @access public
	 * @param mixed $data to clean
	 * @return mixed
	 */
	public function clean_data($data) {
		
		/*
		  * Call this function recursively if $data argument is array.
		  */
		if(is_array($data)) {
			$tmp_arr = array();
			
			/* Clean keys also, if array is associative */
			if($this->lib->array->is_assoc($data)) {
				foreach($data as $key => $value) {
					/* Clean key. pass only a-z, A-Z, 0-9, -, _, . chars */
					$key = $this->strip_chars($key, array('-', '_', '.'));
					/* Clean value */
					$tmp_arr[$key] = $this->clean_data($value);
				} // end foreach
			} else {
				foreach($data as $value) {
					$tmp_arr[] = $this->clean_data($value);
				} // end foreach
			} // end if array is assoc
			
			return $tmp_arr;
			
		} elseif (is_string($data)) {
			
			$data = str_replace(array("\r\n", "\r"), "\n", $data);
			$data = trim($data);
			
			return $data;
		} // end if is_string
		
	} // end func clean_data
	
	/**
	 * Clean data for Cross Site Scripting Hacks.
	 * Do not remove any html tags if second argument $clean_tags is false.
	 *
	 * @access public
	 * @param mixed $data to clean
	 * @param bool $clean_tags remove html tags
	 * @return string
	 */
	function clean_xss($data, $clean_tags = false) {
		
		/*
		 * Call this function recursively if $data argument is array.
		 */
		if(is_array($data)) {
			$tmp_arr = array();
			
			/* Clean keys also, if array is associative */
			if($this->lib->array->is_assoc($data)) {
				foreach($data as $key => $value) {
					/* Clean key. */
					$key = $this->clean_xss($key);
					/* Clean value */
					$tmp_arr[$key] = $this->clean_xss($value);
				} // end foreach
			} else {
				foreach($data as $value) {
					$tmp_arr[] = $this->clean_xss($value);
				} // end foreach
			} // end if array assoc
			return $tmp_arr;
			
		} elseif (is_string($data)) {
			
			/* Remove invisible chars */
			foreach($this->chars_to_remove as $char) {
				$data = preg_replace($this->chars_to_remove, '', $data);
			}
			
			/* Replace dangerous expressions */
			foreach($this->expressions_to_replaced as $etr_k => $etr_v) {
				$data = str_replace($etr_k, $etr_v, $data);
			}
			
			/* Remove dangerous script, etc */
			foreach($this->patterns_to_remove as $ptr_k => $ptr_v) {
				$data = preg_replace($ptr_k, $ptr_v, $data);
			}
			
			if($clean_tags == true) {
				
				/* Strip any html tag */
				$data = $this->strip_tags($data);
				
			} else {
				
				/* Remove any attribute starting with "on*" or "xmlns" */
				$data = $this->strip_attributes($data);
				
				/*
				   * Remove elements which in something like user comments.
					*/
				do {
					$old_data = $data;
					$data = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', '', $data);
				} while ($old_data != $data);
				
				/*
				   * Clean source code, replace javascript and php
				   * vulnerable functions to special chars.
					*/
				if($clean_tags == true) {
					$data = $this->clean_source($data);
				}
				
				/* Strip any script definition */
				$data = $this->strip_scripts($data);
				
			} // end if clean tags
			
			return $data;
		} // end if is_tring(data)
		
	} // end func clean_xss
	
	/**
	 * Fix source code, replace javascript and php
	 * vulnerable functions to special chars.
	 *
	 * example: eval ("echo 333;"); -> eval &#40;"echo 333;"&#41;;
	 *
	 * @access public
	 * @param string data to clean
	 * @return string
	 */
	public function clean_source($data) {
		return preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $data);
	} // end func clean_source
	
	/**
	 * Return cleaned text with new lines for each application run mode.
	 *
	 * @access public
	 * @param  string
	 * @return string
	 * @since  1.1.0
	 */
	public function clean_nl($data) {
		
		if(TK_RUN_MODE == 'cli') {
			return str_replace(
				array("\r\n", "\n\r", "\r", "<br>", "<br />", "<br/>", "<BR>", "<BR />", "<BR/>"),
				"\n",
				$data);
		} else {
			return nl2br($data);
		}
		
	} // end func clean_nl
	
	/**
	 * Clean any string to valid url
	 *
	 * @access public
	 * @param  string $data
	 * @param  mixed (array | null)
	 * @return string
	 * @since  1.4.0
	 */
	public function clean_url_string($data) {
		
		$allowed_chars = array('.', '-', '_');
		$allowed_lets = range('a', 'z');
		$allowed_nums = array('0','1','2','3','4','5','6','7','8','9');
		
		$allowed_all = array_merge($allowed_chars, $allowed_lets, $allowed_nums);
		
		$max = 254;
		$new = '';
		
		$data = str_replace(array("\r\n", "\r", "\n"), '', $data);
		$data = strtolower($data);
		$data = trim($data);
		
		$len = strlen($data);
		
		for($i = 0; $i < $len; $i++) {
			
			$l = substr($data, $i, 1);
			
			if($l == ' ') {
				$l = '-';
			}
			
			if(in_array($l, $allowed_all, true)) {
				$new .= $l;
			}
			
		} // End for
		
		$new = str_replace('---', '-', $new);
		$new = str_replace('--', '-', $new);
		
		$new = substr($new, 0, $max);
		
		$new = trim($new);
		$new = trim($new, '-');
		
		return $new;
		
	} // End func clean_url_string
	
	/**
	 * Clean string as a-z, A-Z, 0-9.
	 * Allow chars defined in $allowed_chars array.
	 *
	 * @access public
	 * @param mixed data to clean
	 * @param array chars to pass
	 * @return string
	 */
	public function strip_chars($data, $allowed_chars = NULL) {
		
		/* Make allowed chars pattern */
		
		$chars_str = '';
		
		if(is_array($allowed_chars)) {
			foreach($allowed_chars as $char) {
				$chars_str .= $char;
			}
		}
		
		return preg_replace("#[^a-z0-9A-Z".$chars_str."]#", '', $data);
		
	} // end func strip_chars
	
	/**
	 * Remove any attribute starting with "on*" or "xmlns"
	 *
	 * @access public
	 * @param string data to clean
	 * @return string
	 */
	public function strip_attributes($data) {
		return preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*["\x00-\x20]?[^>"]*[\'"\x00-\x20]?\s?#iu', '', $data);
	} // end func strip_attributes
	
	/**
	 * Convert tabs to char specified.
	 * By default will convert to empty string.
	 *
	 * @access public
	 * @param string $data
	 * @param string $char
	 * @return string
	 */
	public function strip_tabs($data, $char = '') {
		return str_replace("\t", $char, $data);
	} // end func strip_tabs
	
	/**
	 * Strip image tags.
	 * Allow image source if $keep_src is true.
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_image_tags($data, $keep_src = false) {
		if($keep_src) {
			$src_str = '$1';
		} else {
			$src_str = '';
		}
		
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', $src_str, $data);
		
	} // end func strip_image_tags
	
	/**
	 * Strip hyperlinks.
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_hyperlinks($data) {
		return preg_replace('@<a[^>]*?>.*?</a>@si', '', $data);
	} // end func strip_hyperlinks
	
	/**
	 * Strip meta tags.
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_meta($data) {
		return preg_replace('#<meta\s.*?(?:content\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '', $data);
	} // end func strip_meta
	
	/**
	 * Strip style definitions.
	 *
	 * <style>...</style>
	 * <link href=".." rel="stylesheet" type="text/css">
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_styles($data) {
		return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>)|<style[^>]*>.*?<\/style>/is', '', $data);
	} // end func strip_styles
	
	/**
	 * Strip any script definition.
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_scripts($data) {
		
		$data = preg_replace('@<script[^>]*?>.*?</script>@si', '', $data);
		$data = preg_replace('@<script>@si', '', $data);
		
		$data = preg_replace('@<\?php[^>]*.*?\?>@si', '', $data);
		$data = preg_replace('@<\?[^>]*.*?\?>@si', '', $data);
		
		return $data;
		
	} // end func strip_scripts
	
	/**
	 * Strip comments.
	 * Delete non html comments also if second argument is true.
	 *
	 * <!-- ... -->
	 * /*  * /
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_comments($data, $non_html = true) {
		
		/* remove html comments */
		$data = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $data);
		
		/* remove /* * / comments */
		if($non_html) {
			$data = preg_replace('%/\*[\s\S]+?\*/|(?://).*(?:\r\n|\n)%m', '', $data);
		}
		
		return $data;
		
	} // end func strip_comments
	
	/**
	 * Strip any html tag.
	 *
	 * @access public
	 * @param string data to strip
	 * @return string
	 */
	public function strip_tags($data) {
		return preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $data);
	} // end func strip_tags
	
	/**
	 * Strips extra whitespaces.
	 *
	 * @access public
	 * @param string $data
	 * @return string
	 */
	public function strip_whitespaces($data) {
		
		$data = preg_replace('/[\n\r\t]+/', '', $data);
		return preg_replace('/\s{2,}/', ' ', $data);
		
	} // end func strip_whitespaces
	
	/**
	 * Encode html entity by application encoding.
	 *
	 * @access public
	 * @param mixed array | string data to encode
	 * @return string
	 */
	public function encode_html_entities($data, $encoding = NULL) {
		
		if(is_null($encoding)) {
			$encoding = 'UTF-8';
		}
		
		if(is_array($data)) {
			foreach($data as $key => $value) {
				$data[$key] = $this->encode_html_entities($value, $encoding);
			}
			
			return $data;
			
		} else {
			return htmlentities((string)$data, ENT_QUOTES, $encoding);
		}
		
	} // end func encode_html_entities
	
	/**
	 * Decode html entity by application encoding.
	 *
	 * @access public
	 * @param mixed array | string data to decode
	 * @return string
	 */
	public function decode_html_entities($data, $encoding = NULL) {
		
		if(is_null($encoding)) {
			$encoding = 'UTF-8';
		}
		
		if(is_array($data)) {
			foreach($data as $key => $value) {
				$data[$key] = $this->decode_html_entities($value, $encoding);
			}
			
			return $data;
			
		} else {
			return html_entity_decode(urldecode($data), ENT_QUOTES, $encoding);
		}
		
	} // end func decode_html_entities
	
} /* End of class filter_lib */