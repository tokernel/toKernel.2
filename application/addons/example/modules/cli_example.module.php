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

        $this->lib->cli->out('Welcome! ' . TK_SHORT_NAME, 'green');
        $this->lib->cli->out(TK_DESCRIPTION, 'green');
        $this->lib->cli->out('Version ' . TK_VERSION, 'white');
        
        return true;
    }

    /**
     * Run CLI application in interactive mode (with inserting values)
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example interactive
     */
    public function interactive() {

        $this->lib->cli->out('Hi!', 'yellow');
        $this->lib->cli->out('Enter your name: ', 'white');
        $name = $this->lib->cli->in();
        $this->lib->cli->out('Hello ' . $name, 'green');
        
        return true;
    }

    /**
     * Run CLI application with parameters
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example with_params toKernel framework@tokernel.com
     */
    public function with_params() {

        $this->lib->cli->out('Hello!', 'yellow');
        $this->lib->cli->out('You were called this application with parameters listed bellow:', 'white');
        
        $params = $this->lib->cli->params();

        if(empty($params)) {
            $this->lib->cli->out('There are no params!', 'red');
            exit(1);
        }

        foreach($params as $item => $value) {
            $this->lib->cli->out($item . ': ' . $value, 'light_cyan');
        }
	    
        // Get Parameter from CLI
        $name = $this->lib->cli->params(0);
        
        $this->lib->cli->out('And the first param is: ' . $name, 'green', 'white');
        
        return true;
    }
	
	/*
	 * Another way to get CLI params with $params argument defined.
	 *
	 * Run {path_to_root_of_your_project}/index.php example with_params toKernel framework@tokernel.com
	 */
	public function output_params() {
		$this->lib->cli->out('Hello!', 'yellow');
		$this->lib->cli->out('Parameters defined in constructor', 'white');
		print_r($this->params);
	}
}