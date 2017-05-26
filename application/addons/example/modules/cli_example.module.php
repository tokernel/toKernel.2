<?php
/**
 * Example Module library to run the application in CLI (Command line interface).
 *
 * @version 1.0.0
 */
/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_cli_example_module extends module {
	
	public function __construct($params) {
        parent::__construct($params);
    }

    /**
     * Just display welcome message in CLI screen
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example welcome
     *
     * Another way to call by configured route.
     * # php {path_to_root_of_your_project}/index.php welcome
     *
     * See: application/config/routes.ini
     */
    public function welcome() {

        $this->response->output('Welcome! ' . TK_SHORT_NAME, 'green');
        $this->response->output(TK_DESCRIPTION, 'green');
        $this->response->output('Version ' . TK_VERSION, 'white');
        
        return true;
    }

    /**
     * Run CLI application in interactive mode (with inserting values)
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example interactive
     */
    public function interactive() {

        $this->response->output('Hi!', 'yellow');
        $this->response->output('Enter your name: ', 'white');
        $name = $this->request->in();
        $this->response->output('Hello ' . $name, 'green');
        
        return true;
    }

    /**
     * Run CLI application with parameters
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example with_params toKernel framework@tokernel.com
     */
    public function with_params() {

        $this->response->output('Hello!', 'yellow');
        $this->response->output('You were called this application with parameters listed bellow:', 'white');
        
        $params = $this->request->params();

        if(empty($params)) {
            $this->response->output('There are no params!', 'red');
            exit(1);
        }

        foreach($params as $item => $value) {
            $this->response->output($item . ': ' . $value, 'light_cyan');
        }
	    
        // Get Parameter from CLI
        $name = $this->request->params(0);
        
        $this->response->output('And the first param is: ' . $name, 'green', 'white');
        
        return true;
    }
	
	/*
	 * Another way to get CLI params with $params argument defined.
	 *
	 * Run {path_to_root_of_your_project}/index.php example with_params toKernel framework@tokernel.com
	 */
	public function output_params() {
		$this->response->output('Hello!', 'yellow');
		$this->response->output('Parameters defined in constructor', 'white');
		print_r($this->params);
	}
}