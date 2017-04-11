<?php
Class user_base_addon extends base_addon {
	
	private $email = 'tokernel@example.co.uk';
	
	public function __construct(array $params = array())
	{
		parent::__construct($params);
	}
	
	public function get_email() {
		return $this->email;
	}
}