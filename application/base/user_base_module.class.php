<?php
class example_user_base_module extends base_module {
	
	private $password;
	protected $test_module;
	
	public function __construct(array $params = array())
	{
		parent::__construct($params);
		
		$this->test_module = $this->load_module('views_example');
		
		$this->password = '555';
	}
	
	protected function get_password() {
		
		return md5($this->password);
	}
	
	public function work_with_module() {
		$this->test_module->view_simple();
	}
	
}