<?php
class example_test_base_model extends base_model {
	
	private $table_name = 'ABC_TABLE_XYZ';
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	protected function table_name() {
		return $this->table_name;
	}
}