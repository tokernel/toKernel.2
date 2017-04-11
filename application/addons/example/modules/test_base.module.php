<?php
class example_test_base_module extends example_user_base_module {
	public function __construct(array $params = array())
	{
		parent::__construct($params);
	}
	
	public function run() {
		echo '<h2>This is extended module</h2>';
		echo '<p>In this module the password is:'.$this->get_password().'</p>';
	}
}