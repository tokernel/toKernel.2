<?php
class example_widgets_example_module extends base_module {
	public function __construct(array $params = array())
	{
		parent::__construct($params);
	}
	
	public function widget_without_params() {
		echo '<p>This is the widget without params.</p>';
		echo '<p>This widget defined in template with directly calling module name. So there is no create method in addon class.</p>';
	}
	
	public function widget_with_params($params) {
		echo '<p>This is the widget with params.</p>';
		echo '<pre>';
		print_r($params);
		echo '</pre>';
	}
	
}