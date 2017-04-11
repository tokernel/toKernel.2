<?php
/**
 * Testing module in sub-sub-directory
 */
class example_sub_sub_module extends base_module {
	
	protected $model2;
	
	public function __construct(array $params = array())
	{
		parent::__construct($params);
		
		$this->model2 = $this->load_model('submoddir/subsimple', 'toKernel_mysql_db');
	}
	
	public function say_sub_hi() {
		
		echo 'Hi! from ' .__FUNCTION__. '<br/>';
		
		echo 'And in my end the config value is:' . $this->config->item_get('data', 'EXAMPLE');
		echo '<br />';
		
		echo $this->language('sstitle');
		echo '<br />';
		
		echo '<h1>Now loading views</h1>';
		
		$v1 = $this->load_view('test1');
		$v1->show();
		
		$v1 = $this->load_view(
			'vsub1/vsub2/test2',
			array(
				'a' => 1,
				'b' => 2
			)
		);
		
		$v1->x = 99;
		$v1->y = 100;
		
		$v1->show(
			array(
				'c' => 3,
				'd' => 4
			)
		);
		
		echo '<h4>Data from db</h4>';
		print_r($this->model2->give());
		
	}
	
	public function test_language() {
		
		echo '<p>Display from Module</p>';
		echo $this->language('special', array('David'));
		echo '<p>'.$this->language('_rangelength', array('1', 10)).'</p>';
		
		echo '<p>'.$this->language('my_project_name_is_with_version', array(TK_SHORT_NAME, TK_DESCRIPTION)).'</p>';
		
		echo '<p>Display from view</p>';
		$view = $this->load_view('vsub1/vsub2/test_language');
		$view->show();
		
	}
}