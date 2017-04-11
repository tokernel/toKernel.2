<?php
/**
 * toKernel - Universal PHP Framework.
 * Multi-Language library for application, addons and modules
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
 * language_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class language_lib {
	
	/**
	 * Library object to access all libraries
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;
	
	/**
	 * Loaded language prefix
	 * Example: en | ru
	 *
	 * This value equal to language file extension.
	 *
	 * @access protected
	 * @var string
	 */
	protected $language_prefix;
		
	/**
	 * Language file path
	 *
	 * @access protected
	 * @var string
	 */
	protected $language_file;
	
	/**
	 * Loaded language object
	 * The object will not be loaded until first item request.
	 *
	 * @access protected
	 * @var object
	 */
	protected $language;
		
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		
		$this->lib = lib::instance();
		
		$this->language_prefix = '';
		$this->language_file = '';
				
		$this->language = NULL;
				
	} // end constructor
	
	/**
	 * Magic function will return language prefix.
	 *
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->language_prefix;
	}
	
	/**
	 * Return instance of object library
	 *
	 * @access public
	 * @param string $language_file
	 * @return mixed object | bool
	 */
	public function instance($language_file) {
		
		/* Check if language file exists */
		if(!is_file($language_file)) {
			trigger_error('Language file `' . $language_file . '` not exists!` !', E_USER_ERROR);
			return false;
		}
				
		$this->__construct();
		
		$this->language_file = $language_file;
						
		return clone $this;
		
	} // end func instance
	
	/**
	 * Get language value by item
	 * Using second argument to pass values into item.
	 *
	 * @access public
	 * @param string $item
	 * @param array $lng_args
	 * @return mixed string | bool
	 */
	public function get($item, array $lng_args = array()) {
		
		if(trim($item) == '') {
			trigger_error('Translation expression is empty for language library! (Language file: `'.$this->language_file.'`)', E_USER_ERROR);
			return false;
		}
		
		$return_val = '';
		
		/* load language file to object if not loaded */
		if(!is_object($this->language)) {
			$this->file_load();
		}
	
		/* Define item value if exists */
		if($this->language->item_exists($item)) {
			$return_val = $this->language->item_get($item);
		} else {
			return false;
		}
		
		/* Trigger error even if item exists but value is empty */
		if(trim($return_val) == '') {
			trigger_error('Item `'.$item.'` exists but value is empty in file: `'.$this->language_file .'` !', E_USER_ERROR);
			return false;
		}
		
		// Check if count of replacements greater than 0.
		$rep_count_in_str = mb_substr_count($return_val, '%s');
		$rep_count_in_arr = count($lng_args);
		
		if($rep_count_in_str > $rep_count_in_arr) {
			
			$err_string = htmlspecialchars($item . '='.$return_val);
			
			trigger_error('Too few arguments for translation expression `' . $err_string.'` in ' .
				'language file ('.$this->language_file.').', E_USER_NOTICE);
			
			return false;
		}
		
		/* Parse language expression arguments if not empty */
		if(!empty($lng_args)) {
			return vsprintf($return_val, $lng_args);
		} else {
			return $return_val;
		}
		
	} // end func get
	
	/**
	 * Load language file.
	 *
	 * @access protected
	 * @return void
	 */
	protected function file_load() {
		
		/* Define language prefix from file name */
		$this->language_prefix = $this->lib->file->strip_ext(basename($this->language_file));
		
		/* Load language object */
		$this->language = $this->lib->ini->instance($this->language_file, NULL, true);
		
		/* Check if language object is valid */
		if(!is_object($this->language)) {
			trigger_error('Unable to load language file `'.$this->language_file.'` to object!', E_USER_ERROR);
			return false;
		}
		
	} // end func file_load
	
	/**
	 * Return language prefix
	 * Ex: en | ru
	 *
	 * @access public
	 * @return string
	 */
	public function language_prefix() {
		return $this->language_prefix;
	}

} /* End of class language_lib */