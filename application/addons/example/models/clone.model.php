<?php
class example_clone_model extends example_test_base_model {
	
	private $table;
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function set_table($table) {
		$this->table = $table;
	}
	
	public function get_table() {
		return $this->table_name();
	}
	
}