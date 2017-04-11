<?php
/**
 * Simple model test
 */
Class example_simple_test_model extends base_model {
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function test() {
		
		return $this->db->select_all('articles')->result_array();
		
	}
}