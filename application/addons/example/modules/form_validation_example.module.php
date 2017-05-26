<?php
/**
 * Using Form/Data Validation library
 * Examples of data validations for:
 *  POST global array (form submit).
 *  Other example (can be used in RESTFul API Development).
 *  Custom array data validation.
 */

class example_form_validation_example_module extends base_module {
	
	public function __construct(array $params = array()) {
		parent::__construct($params);
	}
	
	/**
	 * Validating form data (submitted POST request)
	 * http://localhost/my_project/example/form_submit_validation
	 */
	public function form_submit_validation() {
		
		// Initializing the page template
		// Template name: example.form_validation
		// File: /application/templates/some_interface/example.form_validation.tpl.php
		$this->app->set_template(
			'example.form_validation',
			array(
				// Page title
				'page_title' => 'Form validation example'
			)
		);
		
		// Setting validation rules
		/*
		 'form element name' => array of rules (
			// where each element can have more than one rules)
			'rule name' => required value or boolean'
			// If element not have rule "'required' => true" than this rule will validate only if not empty.
		 */
		$validation_rules = array(
			'title' => array(
				'required' => true
			),
			'name' => array(
				'required' => true,
				'rangelength' => array(2, 10)
			),
			'age' => array(
				'digits' => true,
				'range' => array(
					(date('Y') - 100),
					(date('Y') - 18)
				)
			),
			'email' => array(
				'email' => true
			),
			'phone_number' => array(
				'multiple' => array(1, 3),
				'unique' => true,
				'phone_number' => array(
					'#########'
				),
				'required' => true
			)
		);
		
		// Setting rule custom error message
		$validation_messages = array(
			'name' => array(
				'range_length' => 'Your name must be 1-10 characters long'
			),
			'age' => array(
				'range' => 'You have to be 18 years old to submit this code'
			)
		);
		
		// Getting form validation library object
		$fv_lib = $this->lib->form_validation->instance($validation_rules, $validation_messages);
		
		// Do validation if form submitted
		if($this->request->method() == 'POST') {
			// Now depends on the value of $result you can do your staff (for example, insert data into database).
			// By Default, the validation process will work with POST request data.
			$result = $fv_lib->run();
		}

		// Load form view
		// The second array argument if the values used in view file
		// See file: /application/addons/example/modules/form_validation_example/views/form1.view.php
		
		$post_phone_numbers = $this->request->input('phone_number');
		for($i = 0; $i<=2; $i++) {
			if(isset($post_phone_numbers[$i])) {
				$phone_numbers[$i] = $post_phone_numbers[$i];
			} else {
				$phone_numbers[$i] = '';
			}
		}
		
		
		$view = $this->load_view(
			'form1',
			array(
				'messages' => $fv_lib->get_messages(),
				'title' => $this->request->input('title'),
				'name' => $this->request->input('name'),
				'age' => $this->request->input('age'),
				'email' => $this->request->input('email'),
				'phone_number_1' =>$phone_numbers[0],
				'phone_number_2' =>$phone_numbers[1],
				'phone_number_3' =>$phone_numbers[2]
			)
		);
		
		// Display the form
		$view->show();
		
	} // End func form_submit_validation
	
	/**
	 * Validating Other type of request (PUT).
	 * This example of validation you can test by REST API Client via PUT Request type
	 * http://localhost/my_project/example/other_request_validation
	 */
	public function other_request_validation() {
		
		header('Content-Type: text/html; charset=utf-8');
		header('Content-Type: application/json');
		
		// Setting validation rules
		$validation_rules = array(
			'id' => array(
				'required' => true,
				'id' => true
			)
		);
		
		// Set custom message
		$validation_messages = array(
			'id' => array(
				'required' => 'ID required',
			)
		);
		
		// Getting form validation library object
		$fv_lib = $this->lib->form_validation->instance($validation_rules, $validation_messages);
				
		// Now if the PUT request submitted, let's do validation
		if($this->request->method() == 'PUT') {
			
			// We setting the PUT request data to run and validate instead of default POST.
			$result = $fv_lib->run('PUT');
			
			// This is just an example to output json encoded data.
			// NOTICE! In production, you have to manage and organize the output data with correct headers.
			if(!$result) {
				$this->response->set_status(400);
				$data = array(
					'error' => $fv_lib->get_messages()
				);
			} else {
				$data = array(
					'status' => 'OK',
					'data_received' => $this->request->input()
				);
			}
		} else {
			$this->response->set_status(405);
			$data = array(
				'error' => 'Method not allowed'
			);
		}
		
		echo json_encode($data);
		
	} // End func other_request_validation
	
	/**
	 * Validating custom data array
	 * http://localhost/my_project/example/custom_data_validation
	 */
	public function custom_data_validation() {
		
		// Setting validation rules
		$validation_rules = array(
			'id' => array(
				'required' => true,
				'digits' => true
			),
			'credit_card' => array(
				'required' => true,
				'creditcard' => true
			),
			'process_date' => array(
				'date' => 'Y-m-d'
			),
			'phone1' => array(
				'phone_number' => array(
					'+###########',
					'###-###-####',
					'### ### ####',
					'(###) ###-####'
				)
			),
			'phone2' => array(
				'phone_number' => array(
					'+###########',
					'###-###-####',
					'### ### ####',
					'(###) ###-####'
				)
			)
		);
		
		// This is the example of Valid data array
		$valid_data = array(
			'id' => 555,
			'credit_card' => '378734493671000',
			'process_date' => date('Y-m-d'),
			'phone1' => '818-605-0595',
			'phone2' => '+37495565003'
		);
		
		// This is the example of invalid data array
		$invalid_data = array(
			'id' => 0.555,
			'credit_card' => '2117873436710',
			'process_date' => date('Y-m-d') . 'd',
			'phone1' => '818605059',
			'phone2' => '74955650030',
		);
				
		// Getting form validation library object
		$fv_lib = $this->lib->form_validation->instance($validation_rules);
		
		// Now, without any post request or so, we just trying to validate data
		
		// Validating valid data
		echo '<h1>Validating valid data</h1>';
		$result = $fv_lib->run($valid_data);
		var_dump($result);
		
		// Validating invalid data
		echo '<h1>Validating invalid data</h1>';
		$result = $fv_lib->run($invalid_data);
		var_dump($result);
		echo '<pre>';
		print_r($fv_lib->get_messages());
		echo '</pre>';
				
	} // End func custom_data_validation
	
}