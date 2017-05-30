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
		
		// Data to return
		$result = array(
			'interface' => null,
			'request' => array(
				'interface_name' => 'tokernel_default'
			)
		);
		
		// Initialize default interface.
		$interface = $config->section_get('tokernel_default');
				
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
				throw new ErrorException('Interface ['.$interface_name.'] pattern cannot be empty!');
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
				
				$result['request']['interface_name'] = $interface_name;
				
				// Interface matches, Break the loop.
				break;
			}
				
		}
		
		unset($config);
		
		// First Initialization stage is complete
		$result['request']['url'] = $url;
		$result['interface'] = $interface;
		
		// Parse and define URL parts
		$result = self::parse_interface_url($result);
		
		// After URL Initialization, let's parse the language and split the URL parts/params
		$result = self::parse_language_and_params($result);
		
		// Parse The interface Addon/Action
		$result = self::parse_addon_action($result);
		
		// Return interface as array
		return $result;
		
	} // End func parse_interface
	
	private static function parse_interface_url($data) {
		
		$url = $data['request']['url'];
		$pattern = $data['interface']['pattern'];
		
		$data['request']['https'] = 0;
		$data['request']['subdomains'] = array();
		$data['request']['hostname'] = '';
		$data['request']['url_parts'] = '';
		$data['request']['url_params'] = '';
		$data['request']['interface_path'] = '';
		$data['request']['base_url'] = '';
		
		// Define HTTPS
		if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
			$data['request']['https'] = 1;
		}
		
		// Define hostname
		$pos = strpos($url, '/');
		
		if($pos !== false) {
			
			$data['request']['hostname'] = substr($url, 0, $pos);
			
			// Define URL Parts
			$data['request']['url_parts'] = trim(substr($url, $pos), '/');
						
		} else {
			$data['request']['hostname'] = $url;
		}
		
		// Parse sub-domain(s) if any.
		$data['request']['subdomains'] = explode('.', $data['request']['hostname']);
		
		// Define base url if not defined
		if($data['interface']['base_url'] == '') {
			
			if($data['request']['https'] == true) {
				$data['request']['base_url'] = 'https://';
			} else {
				$data['request']['base_url'] = 'http://';
			}
			
			$data['request']['base_url'] .= $data['request']['hostname'];
			
			$build_base_url = true;
		} else {
			$data['request']['base_url'] = $data['interface']['base_url'];
			$build_base_url = false;
		}
		
		// No more things to parse
		if($data['request']['url_parts'] == '' or $pattern == '') {
			$data['request']['url_params'] = $data['request']['url_parts'];
			return $data;
		}
				
		// Define other parts
		$pos = strpos($pattern, '/');
		
		if($pos === false) {
			$data['request']['url_params'] = $data['request']['url_parts'];
			return $data;
		}
			
		$interface_path = substr($pattern, $pos);
		$interface_path = str_replace('*', '', $interface_path);
		$data['request']['interface_path'] = $interface_path;
		
		// Add Interface path to base URL
		if($build_base_url == true) {
			$data['request']['base_url'] .= $data['request']['interface_path'];
		}
		
		$url_params = substr($data['request']['url_parts'], strlen($interface_path));
		$data['request']['url_params'] = trim($url_params, '/');
		
		return $data;
		
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
	
	private static function parse_language_and_params($data) {
		
		// Explode parameters to array
		$data['request']['url_parts'] = self::explode_to_array($data['request']['url_parts']);
		$data['request']['url_params'] = self::explode_to_array($data['request']['url_params']);
		
		// Set Default language prefix.
		$data['request']['language_prefix'] = $data['interface']['default_language'];
		
		// Define Allowed languages array for check.
		$data['request']['allowed_languages'] = explode('|', $data['interface']['allowed_languages']);
		
		// First check, if url parameters is empty, then define language prefix and return interface.
		if(empty($data['request']['url_params'])) {
			$data['request']['language_prefix'] = self::catch_if_browser_language($data);
			return $data;
		}
		
		// Check, if parsing language from URL is not enabled.
		if(!$data['interface']['parse_url_language']) {
			$data['request']['language_prefix'] = self::catch_if_browser_language($data);
			return $data;
		}
		
		// Check, if allowed to catch 
		if(in_array($data['request']['url_params'][0], $data['request']['allowed_languages'])) {
			$data['request']['language_prefix'] = array_shift($data['request']['url_params']);
		} else {
			$data['request']['language_prefix'] = $data['interface']['default_language'];
		}
		
		return $data;
		
	}
	
	private static function catch_if_browser_language($data) {
		
		// Not allowed to catch browser language.
		// Just set Default language of interface.
		if($data['interface']['catch_browser_language'] != 1) {
			return $data['interface']['default_language'];
		}
		
		$browser_languages = lib::instance()->client->languages();
		
		if(empty($browser_languages)) {
			return $data['interface']['default_language'];
		}
		
		foreach($browser_languages as $language) {
			
			$tmp = explode('-', $language);
			$prefix = $tmp[0];
			
			if(in_array($prefix, $data['request']['allowed_languages'])) {
				return $prefix;
			}
			
		}
		
		return $data['interface']['default_language'];
	}
	
	private static function parse_addon_action($data) {
		
		// Set defaults
		$data['request']['addon'] = $data['interface']['default_callable_addon'];
		$data['request']['action'] = $data['interface']['default_callable_action'];
			
		if(empty($data['request']['url_params'])) {
			return $data;
		}
		
		$data = array_merge($data, self::parse_route($data));
		
		$data['request']['addon'] = array_shift($data['request']['url_params']);
			
		if(!empty($data['request']['url_params'])) {
			$data['request']['action'] = array_shift($data['request']['url_params']);
		}
		
		$data['request']['addon'] = strtolower($data['request']['addon']);
		$data['request']['action'] = strtolower($data['request']['action']);
		
		/*
		 * Check, if application allowed to parse URLs with dashed segments.
		 * Example: /addon-name-with-dashes/and-action-name/param-1/param-2
		 * Will parse as:
		 * addon: addon_name_with_dashes
		 * action: and_action_name
		 * url_params: param-1, param-2
		 * Notice: in routes configuration dashes is allowed by default.
		 */
		if($data['interface']['allow_url_dashes'] == 1) {
			$data['request']['addon'] = str_replace('-', '_', $data['request']['addon']);
			$data['request']['action'] = str_replace('-', '_', $data['request']['action']);
		}
		
		return $data;
	}
	
	/**
	 * Parse routing
	 *
	 * @static
	 * @access public
	 * @param array $data
	 * @return array|bool
	 */
	static public function parse_route($data) {
		
		$q_arr = $data['request']['url_params'];
		
		$data['request']['route_parsed'] = '';
				
		$routes_ini = lib::instance()->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'routes.ini');
				
		// Check if this interface inherited another (excepts tokernel_default).
		if($data['interface']['inherited'] != '' and $data['interface']['inherited'] != 'tokernel_default') {
			$parent_interface_routes = $routes_ini->section_get($data['interface']['inherited']);
		} else {
			$parent_interface_routes = array();
		}
		
		// Load actual Routes for interface
		$routes = $routes_ini->section_get($data['request']['interface_name']);
				
		if(empty($routes)) {
			$routes = array();
		}
		
		// Merge if any.
		$routes = array_merge($parent_interface_routes, $routes);
		
		// Now we have to check, if interface routes not empty
		if(empty($routes)) {
			return $data;
		}
		
		$nqs = array();
		
		// Parse each route to detect matching
		foreach($routes as $item => $value) {
			
			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));
			
			$nqs = parent::compare_route($q_arr, $r_arr, $v_arr);
			
			if($nqs !== false) {
				
				$data['request']['route_parsed'] = $item.'='.$value;
				$data['request']['url_params_orig'] = $data['request']['url_params'];
				$data['request']['url_params'] = $nqs;
				
				return $data;
			}
			
		}
				
		unset($routes_ini);
		
		return $data;
		
	} // End func parse_route
	
} /* End class routing */