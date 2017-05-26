<?php
/**
 * toKernel - Universal PHP Framework.
 * Routing class for HTTP and CLI mode
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
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Routing class for HTTP and CLI mode
 *
 * @author David A. <tokernel@gmail.com>
 */
class routing extends routing_core {
	
	public static function parse_http_interface($url) {
		
		$config = lib::instance()->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'http_interfaces.ini');
		
		// Initialize default interface.
		$interface = $config->section_get('tokernel_default');
		$interface['interface_name'] = 'tokernel_default';
		
		foreach($config->sections() as $interface_name) {
			
			// Skip default interface
			if($interface_name == 'tokernel_default') {
				continue;
			}
			
			// Check if interface is enabled.
			if(!$config->item_get('enabled', $interface_name)) {
				continue;
			}
			
			$interface_pattern = $config->item_get('pattern', $interface_name);
			
			// Check if pattern is empty
			if($interface_pattern == '') {
				continue;
			}
			
			$preg_pattern = preg_quote($interface_pattern, '#');
			$preg_pattern = str_replace('\*', '.*', $preg_pattern);
			
			// Return interface name if pattern matches.
			if(preg_match('#^'. $preg_pattern .'$#', $url) === 1) {
				
				// Check if matched interface inherited from any
				$inherited = $config->item_get('inherited', $interface_name);
				
				if($inherited != '') {
					// Merge Interfaces
					$interface = array_merge(
						$interface,
						$config->section_get($inherited)
					);
				}
				
				// Merge interface with default.
				$interface = array_merge(
					$interface,
					$config->section_get($interface_name)
				);
				
				$interface['interface_name'] = $interface_name;
				
				// Interface matches, Break the loop.
				break;
			}
				
		}
		
		unset($config);
		
		// Parse and define URL parts
		$interface['url'] = $url;
		$interface = array_merge(
			$interface,
			self::parse_interface_url($interface)
		);
		
		// After URL Initialization, let's parse the language and split the URL parts/params
		$interface = self::parse_language_and_params($interface);
		
		// Parse The interface Addon/Action
		$interface = self::parse_addon_action($interface);
		
		// Return interface as array
		return $interface;
		
	} // End func parse_interface
	
	private static function parse_interface_url($interface) {
		
		$url = $interface['url'];
		$pattern = $interface['pattern'];
		
		$interface['https'] = 0;
		$interface['subdomains'] = array();
		$interface['hostname'] = '';
		$interface['url_parts'] = '';
		$interface['url_params'] = '';
		$interface['interface_path'] = '';
		
		// Define HTTPS
		if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
			$interface['https'] = 1;
		}
		
		// Define hostname
		$pos = strpos($url, '/');
		
		if($pos !== false) {
			
			$interface['hostname'] = substr($url, 0, $pos);
			
			// Define URL Parts
			$interface['url_parts'] = trim(substr($url, $pos), '/');
						
		} else {
			$interface['hostname'] = $url;
		}
		
		// Parse sub-domain(s) if any.
		$interface['subdomains'] = explode('.', $interface['hostname']);
		
		// Define base url if not defined
		if($interface['base_url'] == '') {
			
			if($interface['https'] == true) {
				$interface['base_url'] = 'https://';
			} else {
				$interface['base_url'] = 'http://';
			}
			
			$interface['base_url'] .= $interface['hostname'];
			
			$build_base_url = true;
		} else {
			$build_base_url = false;
		}
		
		// No more things to parse
		if($interface['url_parts'] == '' or $pattern == '') {
			$interface['url_params'] = $interface['url_parts'];
			return $interface;
		}
				
		// Define other parts
		$pos = strpos($pattern, '/');
		
		if($pos === false) {
			$interface['url_params'] = $interface['url_parts'];
			return $interface;
		}
			
		$interface_path = substr($pattern, $pos);
		$interface_path = str_replace('*', '', $interface_path);
		$interface['interface_path'] = $interface_path;
		
		// Add Interface path to base URL
		if($build_base_url == true) {
			$interface['base_url'] .= $interface['interface_path'];
		}
		
		$url_params = substr($interface['url_parts'], strlen($interface_path));
		$interface['url_params'] = trim($url_params, '/');
		
		return $interface;
		
	} // End func clean_interface_url
	
	// parse url string to array
	private static function explode_to_array($url) {
		
		if(empty($url)) {
			return array();
		}
		
		$arr = explode('/', $url);
		$ret_arr = array();
		
		foreach($arr as $index => $value) {
			if(trim($value) != '') {
				$ret_arr[] = $value;
			}
		}
		
		return $ret_arr;
	}
	
	private static function parse_language_and_params($interface) {
		
		// Explode parameters to array
		$interface['url_parts'] = self::explode_to_array($interface['url_parts']);
		$interface['url_params'] = self::explode_to_array($interface['url_params']);
		
		// Set Default language prefix.
		$interface['language_prefix'] = $interface['default_language'];
		
		// Define Allowed languages array for check.
		$interface['allowed_languages'] = explode('|', $interface['allowed_languages']);
		
		// First check, if url parameters is empty, then define language prefix and return interface.
		if(empty($interface['url_params'])) {
			$interface['language_prefix'] = self::catch_if_browser_language($interface);
			return $interface;
		}
		
		// Check, if parsing language from URL is not enabled.
		if(!$interface['parse_url_language']) {
			$interface['language_prefix'] = self::catch_if_browser_language($interface);
			return $interface;
		}
		
		// Check, if allowed to catch 
		if(in_array($interface['url_params'][0], $interface['allowed_languages'])) {
			$interface['language_prefix'] = array_shift($interface['url_params']);
		} else {
			$interface['language_prefix'] = $interface['default_language'];
		}
		
		return $interface; 
		
	}
	
	private static function catch_if_browser_language($interface) {
		
		// Not allowed to catch browser language.
		// Just set Default language of interface.
		if($interface['catch_browser_language'] != 1) {
			return $interface['default_language'];
		}
		
		$browser_languages = lib::instance()->client->languages();
		
		if(empty($browser_languages)) {
			return $interface['default_language'];
		}
		
		foreach($browser_languages as $language) {
			
			$tmp = explode('-', $language);
			$prefix = $tmp[0];
			
			if(in_array($prefix, $interface['allowed_languages'])) {
				return $prefix;
			}
			
		}
		
		return $interface['default_language'];
	}
	
	private static function parse_addon_action($interface) {
		
		// Set defaults
		$interface['addon'] = $interface['default_callable_addon']; 
		$interface['action'] = $interface['default_callable_action'];
			
		if(empty($interface['url_params'])) {
			return $interface;
		}
		
		$interface = array_merge($interface, self::parse_route($interface));
					
		$interface['addon'] = array_shift($interface['url_params']);
			
		if(!empty($interface['url_params'])) {
			$interface['action'] = array_shift($interface['url_params']);
		} 
		
		$interface['addon'] = strtolower($interface['addon']);
		$interface['action'] = strtolower($interface['action']);
		
		/*
		 * Check, if application allowed to parse URLs with dashed segments.
		 * Example: /addon-name-with-dashes/and-action-name/param-1/param-2
		 * Will parse as:
		 * addon: addon_name_with_dashes
		 * action: and_action_name
		 * url_params: param-1, param-2
		 * Notice: in routes configuration dashes is allowed by default.
		 */
		if($interface['allow_url_dashes'] == 1) {
			$interface['addon'] = str_replace('-', '_', $interface['addon']);
			$interface['action'] = str_replace('-', '_', $interface['action']);
		}
		
		return $interface;
	}
	
	/**
	 * Parse routing
	 *
	 * @static
	 * @access public
	 * @param array $interface
	 * @return array|bool
	 */
	static public function parse_route($interface) {
		
		$q_arr = $interface['url_params'];
		
		$interface['route_parsed'] = '';
				
		$routes_ini = lib::instance()->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'routes.ini');
				
		// Check if this interface inherited another (excepts tokernel_default).
		if($interface['inherited'] != '' and $interface['inherited'] != 'tokernel_default') {
			$parent_interface_routes = $routes_ini->section_get($interface['inherited']);
		} else {
			$parent_interface_routes = array();
		}
		
		// Load actual Routes for interface
		$routes = $routes_ini->section_get($interface['interface_name']);
		
		if(empty($routes)) {
			$routes = array();
		}
		
		// Merge if any.
		$routes = array_merge($parent_interface_routes, $routes);
		
		// Now we have to check, if interface routes not empty
		if(empty($routes)) {
			return $interface;
		}
		
		$nqs = array();
		
		// Parse each route to detect matching
		foreach($routes as $item => $value) {
			
			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));
			
			$nqs = parent::compare_route($q_arr, $r_arr, $v_arr);
			
			if($nqs !== false) {
								
				$interface['route_parsed'] = $item.'='.$value;
				$interface['url_params_orig'] = $interface['url_params'];
				$interface['url_params'] = $nqs;
				
				return $interface;
			}
			
		}
				
		unset($routes_ini);
		
		return $interface;
		
	} // End func parse_route
	
} /* End class routing */