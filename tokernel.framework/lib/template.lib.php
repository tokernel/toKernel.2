<?php
/**
 * toKernel - Universal PHP Framework.
 * Template file loader and parser class library.
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
 * template_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class template_lib {
	
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
	 * Addons object instance to access addons
	 *
	 * @var object
	 * @access protected
	 */
	protected $addons;
	
	/**
	 * Template buffer to parse and interpret
	 *
	 * @var string
	 * @access protected
	 */
	protected $buffer;
	
	/**
	 * Loaded template file
	 *
	 * @var string
	 * @access protected
	 */
	protected $template_name;
	
	/**
	 * Template parsing variables
	 *
	 * @var array
	 * @access protectes
	 */
	protected $template_vars = array();
	
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		
		$this->lib = lib::instance();
		$this->app = app::instance();
		$this->addons = addons::instance();
				
	} // end of func __construct
	
	/**
	 * Clone the object
	 *
	 * @access protected
	 * @return void
	 * @since 3.0.0
	 */
	protected function __clone() {
		$this->buffer = '';
		$this->template_name = '';
		$this->template_vars = array();
	}
	
	/**
	 * Load template file and return cloned instance of this object.
	 *
	 * @access public
	 * @param string $template
	 * @param mixed array | null $template_vars
	 * @param mixed string | null $mode
	 * @return mixed object | bool
	 */
	public function instance($template, $template_vars = NULL, $mode = NULL) {
		
		$obj = clone $this;
				
		tk_e::log_debug('Start load instance.', get_class($this) . '->' . __FUNCTION__ . ' - '.$template);
		
		if(!$obj->load($template, $template_vars, $mode)) {
			tk_e::log_debug('Cannot load template object instance. Template "'. $template.'" not exists.', get_class($this) . '->' . __FUNCTION__ . ' - '.$template);
			trigger_error('Cannot load template object instance. Template "'. $template.'" not exists.', E_USER_WARNING);
			return false;
		}
		
		tk_e::log_debug('End load instance with template "'. $template.'".', get_class($this) . '->' . __FUNCTION__ . ' - ' . $template);
		return $obj;
		
	} // end func instance
	
	
	/**
	 * Load main template file for addon and return buffered data.
	 * Include template file from application dir if exists, else include
	 * from framework dir. Return empty string (as buffer), if template
	 * file not exists in both directories.
	 *
	 * @access protected
	 * @param string $template
	 * @param mixed array | null $template_vars
	 * @param mixed string | null $mode
	 * @return string
	 */
	protected function load($template, $template_vars = NULL, $mode = NULL) {
		
		$template_file = $this->exists($template, $mode);
		
		if($template_file == false) {
			
			tk_e::log_debug('There are no template file to load.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $template);
			return false;
			
		}
		
		$this->template_name = $template;
		
		// Set template variables to parse, if not null.
		if(is_array($template_vars)) {
			$this->template_vars = $template_vars;
		}
		
		/* Get template buffer */
		ob_start();
		
		tk_e::log_debug('Start load template.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
		
		require($template_file);
		$this->buffer = ob_get_contents();
		ob_end_clean();
		
		tk_e::log_debug('End load template with size: ' . strlen($this->buffer) .
			' bytes.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
		
		return true;
		
	} // end func load_template
	
	/**
	 * Parse widget definition tag and return array.
	 * Example of widget definition tag:
	 * <!-- widget addon="some_addon" action="some_action"
	 *      params="param1=param1_value|param2=param2_value" -->
	 *
	 * @access protected
	 * @param string $str (widget definition tag)
	 * @return mixed array | false
	 */
	protected function parse_widget_tag($str) {
		
		$w_arr = array();
		
		/* get addon ID */
		$pos = strpos($str, 'addon="');
		if($pos === false) {
			return false;
		}
		
		$tmp = substr($str, ($pos + strlen('addon="')), strlen($str));
		$tmp = substr($tmp, 0, strpos($tmp, '"'));
		
		if(trim($tmp) == '') {
			return false;
		} else {
			$w_arr['id_addon'] = $tmp;
		}
		
		/* get addon module */
		$pos = strpos($str, 'module="');
		if($pos === false) {
			$w_arr['module'] = '';
		} else {
			$tmp = substr($str, ($pos + strlen('module="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$w_arr['module'] = '';
			} else {
				$w_arr['module'] = $tmp;
			}
		} // end if pos.
		
		/* get addon action */
		$pos = strpos($str, 'action="');
		if($pos === false) {
			$w_arr['action'] = '';
		} else {
			$tmp = substr($str, ($pos + strlen('action="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$w_arr['action'] = '';
			} else {
				$w_arr['action'] = $tmp;
			}
		} // end if pos.
		
		/* get addon params */
		$pos = strpos($str, 'params="');
		if($pos === false) {
			$w_arr['params'] = array();
		} else {
			$tmp = substr($str, ($pos + strlen('params="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$w_arr['params'] = array();
			} else {
				$tmp = explode('|', $tmp);
				
				foreach($tmp as $param) {
					$ptmp = explode('=', $param);
					if(trim($ptmp[0]) != '') {
						if(isset($ptmp[1])) {
							$w_arr['params'][$ptmp[0]] = $ptmp[1];
						} else {
							$w_arr['params'][$ptmp[0]] = NULL;
						}
					}
				} // end foreach
				
			}
		} // end if pos.
		
		return $w_arr;
		
	} // end func parse_widget_tag
	
	/**
	 * Return template file path if exists
	 * if file exist in application custom directory then return file path.
	 * else if file exist in framework directory then return file path.
	 * else return false.
	 *
	 * @access public
	 * @param string $template
	 * @param mixed string | null $mode
	 * @return mixed string | null
	 */
	public function exists($template, $mode = NULL) {
		
		$template_file = $template . '.tpl.php';
		
		/* Create template directory path */
		$templates_dir = 'templates' . TK_DS;
		
		/* Check run mode and create template directory path */
		if(TK_RUN_MODE == TK_CLI_MODE) {
			
			/* If mode is not null, than append to directory */
			if(!is_null($mode)) {
				$templates_dir .= $mode . TK_DS;
			}
			
		} else {
			
			if(is_null($mode)) {
				$mode = $this->app->get_mode();
			}
			
			$templates_dir .= $mode . TK_DS;
			
		}
		
		/* Set template filename */
		$app_template_file = TK_APP_PATH . $templates_dir . $template_file;
		
		if(is_file($app_template_file)) {
			return $app_template_file;
		}
		
		return false;
		
	} // end func exists
	
	/**
	 * Interpreting template and return buffer
	 *
	 * @access public
	 * @param string $replace_this replace "__THIS__" widget with this string
	 * @return string
	 */
	public function run($replace_this = NULL) {
		
		tk_e::log_debug('Start interpret template.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
		
		$runned_widgets_count = 0;
		
		/* Replace/Remove some symbols in template buffer */
		$buffer = $this->buffer;
		
		$buffer = str_replace("\n", '!_TK_NL_@', $buffer);
		$buffer = str_replace("\r", '', $buffer);
		
		$buffer = str_replace('<!--', '<TK_CMT', $buffer);
		$tmp_arr = explode('<TK_CMT', $buffer);
		
		$template_buffer = '';
		
		foreach($tmp_arr as $part) {
			
			/*
			 * If the line is addon widget definition, interpret it.
			 * Example of widget definition tag in template file:
			 * <!-- widget addon="some_addon" action="some_action"
			 *      params="param1=param1_value|param2=param2_value" -->
			 *
			 * NOTE: It is possible to call addon widget in template file,
			 * without widget tag definition. For example:
			 * <?php $this->addons->my_addon->my_action(array('a' => 'b')); ?>
			 * <?php $this->my_action(array('a' => 'b')); ?>
			 */
			
			$part = trim($part);
			
			if(strtolower(substr($part, 0, 6)) == 'widget') {
				$pos = strpos($part, '-->');
				$widget_part = substr($part, 0, $pos);
				
				$tmp_addon_data_arr = $this->parse_widget_tag($widget_part);
				
				if(trim($tmp_addon_data_arr['id_addon']) != '') {
					
					if($tmp_addon_data_arr['id_addon'] == '__THIS__') {
						
						tk_e::log_debug('Appending (main) addon result of __THIS__ ' .
							$this->lib->url->addon() . '->' .
							$this->lib->url->action(). '('.implode(', ',
								$this->lib->url->params()).') ' .
							$tmp_addon_data_arr['action'] . '.',
							get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
						
						$widget_buffer = '';
						
						if(is_null($replace_this)) {
							tk_e::log_debug('Empty __THIS__ widget buffer.',
								get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
						}
						
						$widget_buffer .= $replace_this;
						
						$template_buffer .= $widget_buffer;
						unset($replace_this);
						unset($widget_buffer);
					} else {
						
						if($tmp_addon_data_arr['module'] != '') {
							tk_e::log_debug('Run widget by addon module - "' .
								$tmp_addon_data_arr['id_addon'] . '->' .
								$tmp_addon_data_arr['module'] . '->' .
								$tmp_addon_data_arr['action'] . '" with params "' .
								implode(', ', $tmp_addon_data_arr['params']) .
								'".', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
						} else {
							tk_e::log_debug('Run widget by addon - "' .
								$tmp_addon_data_arr['id_addon'] . '->' .
								$tmp_addon_data_arr['action'] . '" with params "' .
								implode(', ', $tmp_addon_data_arr['params']) .
								'".', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
						}
						
						$template_buffer .= $this->get_widget_runned_buffer($tmp_addon_data_arr);
						$runned_widgets_count++;
					}
				}
				
				unset($tmp_addon_data_arr);
				
				$template_buffer .= substr($part, $pos+3, strlen($part));
			} else {
				if($template_buffer != '') {
					$template_buffer .= '<!--' . $part;
				} else {
					$template_buffer .= $part;
				}
			}
			
		} // end foreach
		
		$template_buffer = str_replace("!_TK_NL_@", "\n", $template_buffer);
		$template_buffer = str_replace("\n\n", "\n", $template_buffer);
		
		/* Parse application global variables and template own variables */
		$vars = array_merge(
			$this->app->get_vars(),
			$this->template_vars
		);
		
		/* Replace all variables */
		if(!empty($vars)) {
			
			tk_e::log_debug('Parsing template total (' . count($vars) . ') variables.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
			
			foreach($vars as $var => $value) {
				$template_buffer = str_replace('{var.'.$var.'}', $value, $template_buffer);
			}
		} else {
			tk_e::log_debug('There are no variables to parse.', get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
		}
		
		$this->buffer = $template_buffer;
		
		tk_e::log_debug('End interpret template with size: - ' . strlen($this->buffer) .
			' bytes. ' .
			' Widgets count: ' . $runned_widgets_count,
			get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name);
		
		
		return $this->buffer;
		
	} // end func run
	
	/**
	 * Run addon->method by widget definition and return buffer of result.
	 *
	 * @access protected
	 * @param array $tmp_addon_data_arr
	 * @return string
	 */
	protected function get_widget_runned_buffer($tmp_addon_data_arr) {
		
		$widget_buffer = '';
		
		/* Call addon action */
		$addon = $this->addons->$tmp_addon_data_arr['id_addon'];
		
		if(!is_object($addon)) {
			tk_e::log_debug('Addon "'.$tmp_addon_data_arr['id_addon'].'" ' .
				'does not an object.',
				get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name
			);
			
			trigger_error('Addon "'.$tmp_addon_data_arr['id_addon'].'" ' .
				'does not an object. ' .
				get_class($this) . '->' . __FUNCTION__ . ' - ' . $this->template_name,
				E_USER_ERROR
			);
			
			/*
			 *
			 * Commented this part, because of possible _call cases.
			 *
			} elseif(!method_exists($addon, $tmp_addon_data_arr['action'])) {
		
				tk_e::log_debug('Method `' . $tmp_addon_data_arr['id_addon'] .
								'->' . $tmp_addon_data_arr['action'].'()` ' .
								'not exists to call in '.__CLASS__ . '->' .
								__FUNCTION__.'() !', __CLASS__);
									
				trigger_error('Method `' . $tmp_addon_data_arr['id_addon'] .
							  '->' . $tmp_addon_data_arr['action'].'()` ' .
							  'not exists to call in '.__CLASS__ . '->' .
								__FUNCTION__.'() !', E_USER_WARNING);
			*/
		} else {
			
			ob_start();
			
			// Option 1. Defined addon module to load
			if($tmp_addon_data_arr['module'] != '') {
				
				$m = $addon->load_module($tmp_addon_data_arr['module'], $tmp_addon_data_arr['params']);
				$m->$tmp_addon_data_arr['action']($tmp_addon_data_arr['params']);
				
				// Option 2. Defined only addon action to call
			} else {
				$addon->$tmp_addon_data_arr['action']($tmp_addon_data_arr['params']);
			}
			
			
			$widget_buffer .= ob_get_contents();
			ob_end_clean();
		}
		
		return $widget_buffer;
		
	} // end func get_widget_runned_buffer
	
} /* End of class template_lib */