<?php
/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 2017-03-18
 * Time: 23:45
 */
class example_clone_module extends base_module {
	
	private $name;
		
	public function __construct(array $params = array())
	{
		parent::__construct($params);
	}
	
	public function __set($item, $name) {
		
		echo '<h2>In the module, before set, I see name is:' . $this->config->item_get('name', 'EXAMPLE') .'</h2>>';
		
		$this->name = $name;
		$this->config->item_set('name', $name, 'EXAMPLE');
	}
	
	public function __get($name) {
		return $this->name;
	}
	
	public function dump_params() {
		echo '<pre>';
		print_r($this->params);
		echo '</pre>';
	}
	
}