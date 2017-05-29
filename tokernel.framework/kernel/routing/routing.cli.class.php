<?php

/* 
 * Routing Functionality for CLI.
 */
class routing extends routing_core {
	
	public static function parse_cli($args) {
		
		$data = array(
			'cli_params' => $args,
			'language_prefix' => 'en',
			'addon' => '',
			'action' => '',
			'route_parsed' => '',
			'cli_params_orig' => $args
		);
		
		$data = array_merge($data, self::parse_route($data));
		
		$data['addon'] = array_shift($data['cli_params']);
		
		if(!empty($data['cli_params'])) {
			$data['action'] = array_shift($data['cli_params']);
		}
		
		$data['addon'] = strtolower($data['addon']);
		$data['action'] = strtolower($data['action']);
		
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

		$data['cli_params_orig'] = $data['cli_params'];
		
		/*
		* Remove first element of $args array,
		* that will be the file name: index.php
		*/
		array_shift($data['cli_params']);
				
		$q_arr = $data['cli_params'];
		
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
								
				$data['route_parsed'] = $item.'='.$value;
				$data['cli_params'] = $nqs;
				
				return $data;
			}
			
		}
						
		unset($routes_ini);
		
		return $data;
		
	} // End func parse_route
	
}
