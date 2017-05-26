<?php

/* 
 * Routing Functionality for CLI.
 */
class routing extends routing_core {
	
	public static function parse_cli_interface($args) {
		
		$interface = array(
			'cli_params' => $args,
			'language_prefix' => 'en',
			'addon' => '',
			'action' => '',
			'route_parsed' => '',
			'cli_params_orig' => $args
		);
		
		$interface = array_merge($interface, self::parse_route($interface));
		
		$interface['addon'] = array_shift($interface['cli_params']);
		
		if(!empty($interface['cli_params'])) {
			$interface['action'] = array_shift($interface['cli_params']);
		}
		
		$interface['addon'] = strtolower($interface['addon']);
		$interface['action'] = strtolower($interface['action']);
		
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
				
		$interface['cli_params_orig'] = $interface['cli_params'];
		
		/*
		* Remove first element of $args array,
		* that will be the file name: index.php
		*/
		array_shift($interface['cli_params']);
		
		$q_arr = $interface['cli_params'];
		
		$routes_ini = lib::instance()->ini->instance(TK_APP_PATH . 'config' . TK_DS . 'routes.ini');
		
		// Cleanup array values
		$q_arr = parent::clean($q_arr);
		
		$routes = $routes_ini->section_get('CLI');
		$nqs = array();
		
		// Parse each route to detect matching
		foreach($routes as $item => $value) {
			
			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));
			
			$nqs = self::compare_route($q_arr, $r_arr, $v_arr);
			
			if($nqs !== false) {
				parent::$args = $nqs;
				
				$interface['route_parsed'] = $item.'='.$value;
				//$interface['cli_params_orig'] = $interface['cli_params'];
				$interface['cli_params'] = $nqs;
				
				return $interface;
			}
			
		}
						
		unset($routes_ini);
		
		return $interface;
		
	} // End func parse_route
	
}
