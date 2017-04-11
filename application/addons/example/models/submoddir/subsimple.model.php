<?php
/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 3/15/2017
 * Time: 10:08 AM
 */
class example_subsimple_model extends base_model {
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function give() {
		return $this->db->select_all('articles', array('title'))->result_object();
	}
	
}