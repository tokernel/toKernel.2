<?php
/**
 * Module located in sub-directory
 */
class example_sub_module extends base_module {
	
	// Using module inside module constructor
	protected $ssmod;
	
	public function __construct(array $params = array())
	{
		parent::__construct($params);
		
		$this->ssmod = $this->load_module('subdir/sub-sub-dir/sub_sub');
		
	}
	
	public function say_hello() {
		echo 'Hello World!';
		
		$this->config->item_set('data', 999, 'EXAMPLE');
		
		echo '<p>Display from module</p>';
		$this->ssmod->say_sub_hi();
				
	}
	
}