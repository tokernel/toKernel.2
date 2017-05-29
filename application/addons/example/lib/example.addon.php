<?php
/**
 * Example addon
 * This addon package demonstrates the main structure and usage of addons in toKernel Framework.
 *
 * @category   addon
 * @package    framework
 * @subpackage addon library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.7.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_addon extends user_base_addon {
	
	protected $model2;
	protected $mod2;
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $params
	 */
	public function __construct($params) {
		parent::__construct($params);
		$this->model2 = $this->load_model('submoddir/subsimple', 'toKernel_mysql_db');
		
		// This module has own language file
		$this->mod2= $this->load_module('subdir/sub-sub-dir/sub_sub');
		
		// This module not have own language file
		
	}
	
    /**
     * Defining methods for:
     * web accessible default (index action).
     * web accessible by name.
     * web accessible only by ajax request.
     * cli accessible (accessible only in command line interface).
     * none accessible (only callable method).
     */

	/**
     * web accessible default (index action).
     *
	 * Default action will called if action not specified in url
     * This is configured in /application/config/application.ini
     *
	 * http://localhost/my_project/ (access without addon or action in URL).
	 */
	public function action_index() {

	    echo 'Welcome! ' . TK_SHORT_NAME . '<br />';
	    echo TK_DESCRIPTION . '<br />';
	    echo 'Version ' . TK_VERSION;

	}

    /**
     * web accessible by name.
     *
     * This method is web accessible because of having "action_" prefix.
     *
     * http://localhost/my_project/example/accessible
     */
    public function action_accessible() {
        echo "Hello This is the web accessible method!";
    }

    /**
     * web accessible only by ajax request.
     *
     * This method is web accessible only by ajax requests because of having "action__ax_" prefix.
     *
     * http://localhost/my_project/example/accessible
     */
    public function action_ax_accessible() {
        echo "Hello This is the web ajax only accessible method!";
    }

	/**
     * Examples of CLI usage
	 *
	 * Arguments in command line defines the way which addon and action will be called with patams.
	 *
	 * # php {path_to_root_of_your_project}/index.php {addon_to_call} {action_to_call} {param1} {paramN}
	 *
     * Notice: To access (call/run) methods in command line, the method names should start with "cli_" prefix.
     * See: http://tokernel.com/framework/documentation/class-libraries/cli
     *
     * Methods listed bellow, loads and uses module /application/addons/example/modules/cli_example.module.php
     */

    /**
     * cli accessible only in command line interface.
     *
     * This method accessible only in command line interface because of having "cli_" prefix.
     *
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
    public function cli_welcome() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->welcome();
    }

    /**
     * Run CLI application in interactive mode (with inserting values)
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example interactive
     */
    public function cli_interactive() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->interactive();
    }

    /**
     * Run CLI application with parameters
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example with_params toKernel framework@tokernel.com
     */
    public function cli_with_params() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->with_params();
    }
    
    /*
     * Another way to get CLI params with $params argument defined.
     *
     * Run {path_to_root_of_your_project}/index.php example with_params_other toKernel framework@tokernel.com
     */
    public function cli_with_params_other(array $params = array()) {
	    $cli_module = $this->load_module('cli_example', $params);
	    $cli_module->output_params();
    }

    /**
     * Output CLI colors
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php example colors
     *
     * Also it is possible to run this action with other, routes name
     * # php {path_to_root_of_your_project}/index.php show-your-colors
     *
     * See: application/config/routes.ini
     */
    public function cli_colors() {
        $this->response->output_colors();
    }

    /**
     * Examples of MySQL Database library usage
     * Notice: Before run, please read and complete setup instructions /install/Install.txt
     * See also: http://tokernel.com/framework/documentation/class-libraries/mysql
     *
     * Actions bellow, loads and uses module /application/addons/example/modules/db_example.module.php
     *
     * Notice: To access web pages in addon library,
     * all methods accessible via web interface should have "action_" prefix.
     */

    /**
     * Insert record into MySQL Database table
     *
     * http://localhost/my_project/example/db_insert
     */
    public function action_db_insert() {
        $db_module = $this->load_module('db_example');
        $db_module->insert();
    }

    /**
     * Update record in MySQL Database table
     *
     * http://localhost/my_project/example/db_update
     */
    public function action_db_update() {
        $db_module = $this->load_module('db_example');
        $db_module->update();
    }

    /**
     * Delete record in MySQL Database table
     *
     * http://localhost/my_project/example/db_delete
     */
    public function action_db_delete() {
        $db_module = $this->load_module('db_example');
        $db_module->delete();
    }

    /**
     * Select records from MySQL Database table
     *
     * http://localhost/my_project/example/db_select
     */
    public function action_db_select() {
        $db_module = $this->load_module('db_example');
        $db_module->select();
    }

    /**
     * Other examples of mysql class library
     *
     * http://localhost/my_project/example/db_other
     */
    public function action_db_other() {
        $db_module = $this->load_module('db_example');
        $db_module->other();
    }

    /**
     * Examples of Templates and widgets usage
     * Actions bellow will demonstrate you the templates and widgets functionality in project.
     *
     * Notice: Template files located in /application/templates/some_interface/ directory
     */

    /**
     * Load template with setting the name.
     *
     * http://localhost/my_project/example/template_by_name
     */
    public function action_template_by_name() {

        // Setting the application template to process.
        $this->app->set_template('example.my_template');

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/some_interface/example.my_template.tpl.php
        echo 'This is the addon action output.';
    }

    /**
     * Load template by default, because the action name template names are equal.
     *
     * http://localhost/my_project/example/template_by_default
     */
    public function action_template_by_default() {

        // We were not setting any template name, but the default template of this action
        // will be loaded, because the template and addon.action names are equal.

        // template name: example.by_default
        // addon.action name: example.by_default

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/some_interface/example.template_by_default.tpl.php
        echo 'This is the addon action output.';
    }

    /**
     * Load template with widgets
     *
     * http://localhost/my_project/example/template_with_widgets
     */
    public function action_template_with_widgets() {

        // Setting the application template to process.
        $this->app->set_template('example.my_template_with_widgets');

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/some_interface/example.my_template.tpl.php
        echo 'This is the addon action output.';

    }
	
	/**
	 * Load template with module specific widgets
	 *
	 * http://localhost/my_project/example/template_with_module_widgets
	 */
	public function action_template_with_module_widgets() {
		
		// Setting the application template to process.
		// The second argument array specifies the values to parse in template.
		$this->app->set_template(
			'example.my_template_with_module_specific_widgets',
			array(
				'app_name' => TK_SHORT_NAME,
				'app_description' => TK_DESCRIPTION
			)
		);
		
		// This output will display in template widget named "__THIS__".
		// See: /application/templates/some_interface/example.my_template_with_module_specific_widgets.tpl.php
		echo 'This is the addon action output.';
		
	}
	
    /**
     * Widget without parameters.
     *
     * This widget method called from template file:
     * /application/templates/some_interface/example.my_template_with_widgets.tpl.php
     *
     * as:
     * <!-- widget addon="example" action="widget_without_params" -->
     *
     * As you can see, this method is not possible to call by HTTP request because it not have "action_" prefix.
     */
    public function widget_without_params() {

        // This content will output in template file where the widget defined;
        echo '<h2>This is content from first widget</h2>';

    }

    /**
     * Widget with parameters.
     *
     * This widget method called from template file:
     * /application/templates/some_interface/example.my_template_with_widgets.tpl.php
     *
     * as:
     * <!-- widget addon="example" action="widget_with_params" params="project=My Project|version=1.0.0 alpha" -->
     */
    public function widget_with_params($params) {

        // This content will output in template file where the widget defined;
        echo '<h2>This is content from second widget with parameters:</h2>';

        echo '<p>';
        foreach ($params as $item => $value) {
            echo $item . ': ' . $value . '<br />';
        }
        echo '</p>';

    }

    /**
     * Examples of views (View files) usage
     *
     * The views are HTML content formatted files with output content.
     */

    /**
     * Using just one simple view file.
     *
     * http://localhost/my_project/example/view_simple
     */
    public function action_view_simple() {

        // Load the view object
        $view = $this->load_view('simple');
	    
        echo 'Config before:' . $this->config->item_get('data', 'EXAMPLE');
        echo '<br />';
        
        // Output the view HTML content to screen.
        $view->show();
	
	    echo 'Config after:' . $this->config->item_get('data', 'EXAMPLE');
	    echo '<br />';
    }

    /**
     * Using more then one views in views
     * In ths second view we will set parameters (to display).
     *
     * http://localhost/my_project/example/view_more_than_one
     */
    public function action_view_more_than_one() {

        // Load the view object
        $view1 = $this->load_view('simple');

        // Output the view HTML content to screen.
        $view1->show();

        // Load second view file
        $view2 = $this->load_view('simple_with_params');

        // Set values to view file
        $view2->project_name = 'My project';
        $view2->project_version = '1.0.0 alpha';

        // In this case, we will get the parsed content of view, then output.
        $parsed_view = $view2->run();

        echo $parsed_view;

    }

    /**
     * Example of run template with views
     *
     * http://localhost/my_project/example/template_with_view
     */
    public function action_template_with_view() {

        $this->app->set_template('example.my_template');

        // Load second view file
        $view = $this->load_view('simple_with_params');

        // define values for view file
        // In this case we will use another approach to set values to view
        $data = array(
            'project_name' => 'My project',
            'project_version' => '1.0.0 alpha'
        );

        // Get parsed HTML Content
        $parsed_view = $view->run($data);

        echo $parsed_view;
    }

    /**
     * View files used by module
     *
     * Each module can have own modules located in modules/{module_name}/views directory.
     * In our example, the module file is: /application/addons/example/modules/views_example.module.php
     * Where view files of module located in: /application/addons/example/modules/views_example/view/
     *
     * http://localhost/my_project/example/view_of_module
     */
    public function action_view_of_module() {

        $module = $this->load_module('views_example');
        $module->view_simple();

    }

    /**
     * toKernel framework also supports large scale of multi-language functionality.
     *
     * Main default language files located in /application/languages/
     * where each language file name denied as language prefix.
     *
     * For example, the English language file will be "en.ini"
     * and the Russian language file "ru.ini"
     *
     * Each of "addon" in toKernel framework also can have own language files.
     * This approach gives us possibility to separate addon language files from others.
     *
     */

    /**
     * Using Application main languages located in application/languages/
     * $this->app->language('your_language_expression')
     *
     * Using Addon specific languages located in application/addons/example/languages/
     * $this->language('your_language_expression')
     *
     * NOTICE: In Views and Modules you also can use $this->language() method.
     *
     * http://localhost/my_project/example/multi_language
     *
     */
    public function action_multi_language() {


        // Using
        /*
        $this->app->language('err_subject_production');
        $this->language('msg_error_encountered');
        $this->language(
             'my_project_name_is_with_version',
             'My project',
             '1.0.0 alpha'
        );
        */

        // NOTICE! In real live, the content is better to echo by view file, instead of echo each HTML tag.
        // This is Just en example action to demonstrate multi-language functionality.

        echo '<h1>Multi-languages usage</h1>';

        echo '<h2>Application Language usage</h2>';
        echo '<p>';
        echo 'The string (error message) listed bellow loaded from application language:<br/>';
        echo '<strong>' . $this->app->language('err_subject_production') . '</strong>';
        echo '</p>';

        echo '<h2>Addon Language usage</h2>';
        echo '<p>';
        echo 'The string (error message) listed bellow loaded from addon language:<br/>';
        echo '<strong>' . $this->language('msg_error_encountered') . '</strong>';
        echo '</p>';


        echo '<h2>Language usage with additional arguments in</h2>';
        echo '<p>';
        echo 'The string listed bellow loaded from addon language with additional 2 values:<br/>';
        echo 'We adding the project name and version into language string<br/>';
        echo '<strong>' .
                $this->language(
                    'my_project_name_is_with_version',
                    'My project',
                    '1.0.0 alpha'
                )
            . '</strong>';
        echo '</p>';
    }

    /**
     * Working with libraries
     *
     * Libraries locates in /tokernel.framework/lib
     * and can be extended/overridden in /application/lib
     *
     * http://localhost/my_project/example/lib_usage
     */
    public function action_lib_usage() {

        // Let's validate some data with validation library.
        var_dump($this->lib->valid->email('wrong-email'));

        /*
         * If the library called once, the same copy of object will
         * be returned next time when we call again.
         * In this example, same copy of loaded object
         * (validation class library) will be used.
         */
        var_dump($this->lib->valid->id(55));

        /*
         * Now, let's get 2 different instances of library object
         */
        // in this example we not using any constructor arguments.
        $params = NULL;

        // Define an instance of Pagination class library
        $p1 = $this->lib->load('pagination', $params, true);
        // The last argument "true" assumes that the new instance of library will be returned.
	    
	    $offset = $this->request->url_params(0);
	    	    
        $base_url = $this->lib->url->url('example', 'lib_usage', array(0 => '{var.offset}', 2 => 'some-other-arg'));
        echo $p1->run(155, 10, $offset, $base_url);
	    
        // Define a new instance if same library
        $p2 = $this->lib->load('pagination', $params, true);
        echo $p2->run(1050, 10, $offset, $base_url);

    }

    /**
     * Extending class libraries into application.
     *
     * In the toKernel framework we have shopping_cart library:
     *  /tokernel.framework/lib/shopping_cart.lib.php
     * and it is extended in application as:
     *  /application/lib/shopping_cart.ext.lib.php
     *
     * The items_get_json() method added to extended library.
     *
     * http://localhost/my_project/example/extended_lib_usage
     */
    public function action_extended_lib_usage() {

        // Define shopping cart library
        $s = $this->lib->shopping_cart;

        // Reset! Clean session if already set.
        $s->reset();

        // Add products
        $s->item_set(
            $item = array(
                'price' => 25.5,
                'quantity' => 2,
                'name' => 'PHP book'
            )
        );

        $s->item_set(
            $item = array(
                'price' => 1500,
                'quantity' => 1,
                'name' => 'How to be happy?'
            )
        );

        // Get products
        // This method defined in parent class library
        print_r($s->items_get());

        // This method defined in extended class library
        print_r($s->items_get_json());

    }
    
    public function action_modsubdir() {
    	
    	$mod = $this->load_module('subdir/sub');
    	
    	$data = $this->config('data', 'EXAMPLE');
    	echo $data . '<br />';
    	
    	$mod->say_hello();
	    echo '<br />';
	    
	    $data = $this->config('data', 'EXAMPLE');
	    echo $data . '<br />';
	    
	    //$subsubm = $this->load_module('subdir/sub-sub-dir/sub_sub');
	    //$subsubm->say_sub_hi();
	    
    }
    
    public function action_model_simple() {
    	$model = $this->load_model('simple_test', 'toKernel_mysql_db');
	    print_r($model->test());
	    
	    
	    print_r($this->model2->give());
    }
    
    public function action_test_language() {
	    
    	echo '<p>This is in addon</p>';
	    echo $this->language('_rangelength', array(15, 105));
	
	    echo '<p>'.$this->language('my_project_name_is_with_version', array(TK_SHORT_NAME, TK_DESCRIPTION)).'</p>';
	
		echo '<p>This is in addon\'s view file</p>';
		$v = $this->load_view('lng_test');
		$v->show();
		
		////
	    $this->mod2->test_language();
	    
    }
    
    public function action_clone_module() {
    	
    	echo '<h1>New Clones of module</h1>';
    	
    	echo '<h2>Config name in addon start: ' . $this->config('name', 'EXAMPLE') . '</h2>';
    	
    	$m1p = array('age' => 37);
    	$m1 = $this->load_module('clone', $m1p, true);
    	$m1->name = 'Dato';
	
	    echo '<h2>Config name in module 1 start: ' . $this->config('name', 'EXAMPLE') . '</h2>';
	    
    	echo 'Module 1: ' . $m1->name . '<br />';
	
    	$m1->dump_params();
	
	    $m2p = array('work' => 'developer');
	    $m2 = $this->load_module('clone', $m2p, true);
	    $m2->name = 'Ayvik';
	
	    echo 'Module 2: ' . $m2->name . '<br />';
	    $m2->dump_params();
	
	    echo '<h2>Config name in module 2 start: ' . $this->config('name', 'EXAMPLE') . '</h2>';
	    
	    echo 'Module 1: ' . $m1->name . '<br />';
	    $m1->dump_params();
	
	    echo '<h2>Config name in module 1 start: ' . $this->config('name', 'EXAMPLE') . '</h2>';
	    
	    echo '<h1>New Clones of model</h1>';
	    
	    echo '<p>model 1</p>';
	    $mod1p = array('id_user' => 33);
	    $mod1 = $this->load_model('clone', null, true);
	    $mod1->set_table('users');
	    echo $mod1->get_table() . '<br />';
		//$mod1->dump_params();
		
	    echo '<p>model 2</p>';
	    $mod2p = array('id_admin' => 99);
	    $mod2 = $this->load_model('clone', null, true);
	    $mod2->set_table('admins');
	    echo $mod2->get_table() . '<br />';
	    //$mod2->dump_params();
    }
    
    public function action_test_base_addon() {
    	echo '<h1>Base addon test</h1>';
    	echo '<p>The email in base class is:' . $this->get_email() . '</p>';
	
	    echo '<h1>Base module test</h1>';
	    $mod = $this->load_module('test_base');
	    $mod->run();
	    
	    echo '<h2>Testing module loading in extended module constructor</h2>';
	    $mod->work_with_module();
	
	    echo '<h2>Testing base model</h2>';
	    $model = $this->load_model('clone');
	    echo $model->get_table();
	    	    	    
    }
	
	/**
	 * Using Form/Data Validation library
	 * Examples of data validations for:
	 *  - POST global array (form submit).
	 *  - Other example (can be used in RESTFul API Development).
	 *  - Custom array data validation.
	 *
	 * See: /application/addons/example/modules/form_validation_example.module.php
	 */
	
	/**
	 * Validating form data (submitted POST request)
	 * http://localhost/my_project/example/form_submit_validation
	 */
	public function action_form_submit_validation() {
		
		$module = $this->load_module('form_validation_example');
		$module->form_submit_validation();
		
	}
	
	/**
	 * Validating Other type of request (PUT).
	 * http://localhost/my_project/example/other_request_validation
	 */
    public function action_other_request_validation() {
    	
    	$module = $this->load_module('form_validation_example');
	    $module->other_request_validation();

    }
	
	/**
	 * Validating custom data array
	 * http://localhost/my_project/example/custom_data_validation
	 */
	public function action_custom_data_validation() {
		
		$module = $this->load_module('form_validation_example');
		$module->custom_data_validation();
		
	}
	
	// Testing Request / Response in CLI
	public function cli_r() {
		
		$this->response->output_usage('Just testing! :)');
		
		$this->response->output(
			$this->request->cli_params(0),
			'yellow',
			'red'
		);
		
		$this->response->output('A', 'white');
		
	}
	
	// Testing request and response
	public function action_r() {
		
		$content = 'Hello World!';
		
		// Option 1. Just directly echo the content
		$this->response->set_status(401);
		//$this->response->set_headers('Content-Type: text/plain');
		
		foreach($GLOBALS as $key => $value) {
			echo $key . "<br/>";
		}
		
		echo $content;
		
		return true;
				
		// Option 2. Set response content
		$this->response->set_status(401);
		$this->response->set_headers('a', 'b');
		$this->response->set_content($content);
		
		return true;
				
		///// Cases when json/API //////////
		
		// Case 1. Just directly echo json
		$response_data = array(
			'a' => 1,
			'b' => 2
		);
		
		$this->response->set_headers('Content-Type: Application/json');
		echo json_encode($response_data);
		
		return true;
		
		// Case 2. Set response content
		// Response will know to add json type to headers
		$this->response->set_content($response_data);
		
		return true;
				
	}
	
} // end class
