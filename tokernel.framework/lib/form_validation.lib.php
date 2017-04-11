<?php
/**
 * toKernel - Universal PHP Framework.
 * Form validation class library.
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.4.0
 *
 * This Library uses application languages.
 *
 * Possible rules:
 *
 * 'required' => true, // if set value as false, the validation will passed as true.
 * 'min_length' => 5, // String length should be minimum of 5 characters
 * 'max_length' => 10, // String length should be maximum of 10 characters
 * 'range_length' => array(5, 10), // String length should be in range of 5-10 characters
 * 'min' => 5, // Number value should be minimum 5
 * 'max' => 10, // Number value should be maximum 10
 * 'range'	=> array(5, 10), // Number value should be in range of 5-10
 * 'equal_to' => 'password', // The value should be equal to value of element named "password" (this is useful for password confirmation)
 * 'different_from' => 'phone1', // The value should be different from value of element named "phone1" (this is useful to except inserting same values in different elements)
 * 'in' => array('home', 'garage', 'office'), // Is equal to one of array elements
 * 'one_of' => array('phone1', 'phone2', 'phone2'), // Required to enter minimum one of element(s).
 * 'date' => 'Y-m-d', // Date in given format
 * 'date_iso' => true, // Date in ISO standard Y-m-d (i.e. 2014-02-07)
 * 'date_before' => date('Y-m-d'), // Date is earlier than the given date
 * 'date_after' => date('Y-m-d'), // Date is later than the given date
 * 'date_between' => array('2011-09-22', '2017-04-12'), // Date should be between two dates.
 * 'number' => true, // Any type of number (i.e. 55 | 55.55)
 * 'digits' => true, // Integer number only (i.e. 55)
 * 'id' => true // Any ID start from 1 (i.e. 1 | 100 | 998547)
 * 'alpha' => true, // String with letters only: a-zA-Z (i.e. abc | ABC | abcXYZ )
 * 'alphanumeric' => true, // String and integer numbers only (i.e. ABC435test546)
 * 'email' => true, // Should be valid Email address
 * 'credit_card' => true, // Should be valid Credit card number (i.e. 378734493671000 | 6011000990139424)
 * 'phone_number' => true, // Should be valid phone number (i.e. 818-605-0595 | 8186050595 | 818 605 0595 | 1(818) 605-0595)
 * 'url' => true, // Should be valid URL
 * 'regex' => /^[A-Za-z_]{4,8}$/, // Matches to regular expression pattern
 * 'multiple' => array(2, 4), // The value should be array and contains 2 - 4 none empty items.
 * 'multiple' => true, // The value must be an array
 * 'unique' => true, // This works only with 'multiple' rule and assume, that all element values should be unique.
 *
 * This rule you can use in javascript validation (i.e. jQuery validation plugin).
 *
 * 'remote' => array(
 *     'url' => 'http://example.com/user/check_email_exists/',
 *     'message_manual' => 'This is manual message',
 *     'message_email_already_registered' => 'email',
 * ),
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Form validation class library.
 *
 * @author David A. <tokernel@gmail.com>
 * @author Karapet S. <join04@yahoo.com>
 */
class form_validation_lib {

    /**
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access protected
     */
    protected $lib;

    /**
     * Main Application object for
     * accessing app functions from this class
     *
     * @var object
     * @access protected
     */
    protected $app;

    /**
     * Rules array
     *
     * @access protected
     * @var array
     */
    protected $rules = array();

    /**
     * Messages array
     *
     * @access protected
     * @var array
     */
    protected $messages = array();

    /**
     * Custom messages for rules
     *
     * @access protected
     * @var array
     * @since 2.0.0
     */
    protected $rule_custom_messages = array();
	
	/**
	 * Data to validate
	 *
	 * @access protected
	 * @var array
	 * @since Version 2.0.0
	 */
	protected $validation_data;
	
    /**
     * Final validation result
     *
     * @access protected
     * @var boolean
     */
    protected $validation_result = true;


    /**
     * Class constructor
     *
     * @access public
     */
    public function __construct() {
        $this->app = app::instance();
        $this->lib = lib::instance();
    } // end func __construct

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        $this->rules = array();
        $this->messages = array();
        $this->rule_custom_messages = array();
    } // end func __destruct
	
	/**
	 * Clone the object
	 *
	 * @access protected
	 * @return void
	 */
	protected function __clone() {
		$this->rules = array();
		$this->messages = array();
		$this->rule_custom_messages = array();
		$this->validation_result = true;
	}
	
	/**
     * Return a new clean instance of this object
     *
     * @access public
     * @param mixed array | NULL $rules
     * @param mixed array | NULL $custom_messages
     * @return object
     * @since 2.0.0
     */
    public function instance($rules = null, $custom_messages = null) {
	        	
        $obj = clone $this;
        
        if(!is_null($rules)) {
            $obj->add_rules($rules, $custom_messages);
        }

        return $obj;

    } // end func instance

    /**
     * Add validation rules
     *
     * Example:
     *
     * $this->lib->form_validation->add_rules(
     *		'email' => array (
     *			'email' => true,
     *			'required' => true
     *		),
     *		'price' => array(
     *			'range'	=> array(9, 99),
     *			'required' => true
     *		)
     * );
     *
     * @access public
     * @param array $rules
     * @param mixed array | NULL $custom_messages
     * @return void
     */
    public function add_rules($rules, $custom_messages = array()) {
        
    	$this->rules = $rules;
        $this->rule_custom_messages = $custom_messages;
	    
    } // end func add_rule

    /**
     * Run Form validation
     * By default the validation process will work with POST request data.
     *
     * @access public
     * @param string | array $validation_data
     * @return boolean
     */
    public function run($validation_data = 'POST') {
	
	    // Notice: This can be a name of Request (POST | PUT | DELETE) or an assoc array.
    	switch($validation_data) {
		    case 'POST':
		    case 'PUT':
		    case 'DELETE':
			    $this->validation_data = $validation_data;
			    break;
		    default:
		    	if(is_array($validation_data)) {
				    $this->validation_data = $validation_data;
			    } else {
				    trigger_error('Unacceptable validation data type.', E_USER_WARNING);
				    return false;
			    }
	    }
	            
        foreach($this->rules as $element => $rules) {
			
        	$value = $this->get_value($element);
	        	
            foreach($rules as $rule => $rule_values) {
				
				if(isset($rules['multiple'])) {
					$this->validate_multiple_rule($element, $value, $rule, $rule_values);
				} else {
					$this->validate_rule($element, $value, $rule, $rule_values);
				}
            }
        }

		return $this->validation_result;

    } // end func run

    /**
     * Get error messages
     *
     * @access public
     * @param mixed $element
     * @return mixed
     */
    public function get_messages($element = NULL) {

        if(is_null($element)) {
            return $this->messages;
        }

        if(isset($this->messages[$element])) {
            return $this->messages[$element];
        }

        return false;
    } // end func get_messages

    /**
     * Print message with html tag for element
     * This method can be called under the html form element
     *
     * @access public
     * @param string $element
     * @param mixed string | NULL $tag
     * @return boolean
     */
    public function print_message($element, $tag = NULL) {

        $messages = $this->get_messages($element);

        if(!$messages) {
            return false;
        }

        if(is_null($tag)) {
            $tag = '<label for="'.$element.'" class="error">{var.messages}</label>';
        }

        $messages_str = '';

        foreach($messages as $message) {
            $messages_str .= $message . '<br />';
        }

        $messages_str = rtrim($messages_str, '<br />');

        $messages_str_result = str_replace($tag, '{var.messages}', $messages_str);

        echo $messages_str_result;

        return true;
    } // end func print_message

    /**
     * Set message for element
     *
     * @access public
     * @param string $element
     * @param string $message
     * @return void
     */
    public function set_message($element, $message) {
		$this->messages[$element][] = $message;
		$this->validation_result = false;
    } // end func set_message

    /**
     * Validate element value and set message
     *
     * @access protected
     * @param string $element
     * @param mixed $value
     * @param string $rule
     * @param mixed $rule_values
     * @return boolean
     */
    protected function validate_rule($element, $value, $rule, $rule_values) {
	     
        // Pass empty value if not marked as required
        if($rule != 'required' and ($value === '' || $value === false)) {
	        return true;
        }
	    
        switch($rule) {
        	
        	// Required
	        case 'required':
		        // Check rule only if value is true
		        if($rule_values == false) {
			        return true;
		        }
		
		        if($this->lib->valid->required($value) == true) {
			        return true;
		        }
		        
	        	break;
		
		    // Minimum length
	        // @deprecated
	        case 'minlength':
	        case 'min_length':
	            
	        	if($this->lib->valid->required($value, $rule_values) == true) {
		            return true;
	            }
	            
	        	break;
	
	        // Maximum length
	        // @deprecated
	        case 'maxlength':
	        case 'max_length':
		
		        if($this->lib->valid->required($value, -1, $rule_values) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Range length
	        // @deprecated
	        case 'rangelength':
	        case 'range_length':
		
		        if($this->lib->valid->required($value, $rule_values[0], $rule_values[1]) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Minimum
	        case 'min':
		
		        if($value >= $rule_values and is_numeric($value)) {
			        return true;
		        }
		        
		        break;
	
	        // Maximum
	        case 'max':
		
		        if($value <= $rule_values and is_numeric($value)) {
			        return true;
		        }
		        
		        break;
	
	        // Range
	        case 'range':
		
		        if($value >= $rule_values[0] and $value <= $rule_values[1] and is_numeric($value)) {
			        return true;
		        }
		        
		        break;
	
	        // Equal to
	        case 'equal_to':
		
		        if($value == $this->get_value($rule_values)) {
			        return true;
		        }
		        
		        break;
	
	        // Different from
	        case 'different_from':
		
		        if($value != $this->get_value($rule_values)) {
			        return true;
		        }
		        
		        break;
	
	        // In
	        case 'in':
		
		        if(in_array($value, $rule_values)) {
			        return true;
		        } else {
			
			        /*
			         * Define message for "in" rule.
					 * Example:
					 *
					 * Rule defined as: 'in' => array('Home', 'Garage', 'Office')
					 * Will display message as: Possible values to enter is: Home, Garage, Office
					 */
			        $rule_values = implode(', ', $rule_values);
			
		        }
		        
		        break;
	
	        // One of
	        case 'one_of':
			
			    foreach($rule_values as $element_name) {
				    if($this->get_value($element_name) != '') {
					    return true;
				    }
			    }
		
		        /*
				 * Define message for "one_of" rule.
				 * Example:
				 *
				 * Rule defined as: 'one_of' => array('phone1', 'phone2', 'phone3')
				 * Will display message as: Please enter at least 1 of this elements phone1, phone2, phone3.
				 */
			    $rule_values = implode(', ', $rule_values);
		    
		        break;
	
	        // Date
	        case 'date':
		
		        if($this->lib->valid->date($value, $rule_values) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Date iso
	        case 'date_iso':
		
		        if($this->lib->valid->date_iso($value) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Date before
	        case 'date_before':
		
		        if($this->lib->date->is_passed($value, $rule_values) == false) {
			        return true;
		        }
		        
		        break;
	
	        // Date after
	        case 'date_after':
		
		        if($this->lib->date->is_passed($value, $rule_values) == true) {
			        return true;
		        }
		        
		        break;
		        
		    // Date between
	        case 'date_between':
		
		        if($this->lib->valid->date($value, 'Y-m-d') == true
			        and
		        	$this->lib->date->is_passed($value, $rule_values[0]) == true
			        and
			        $this->lib->date->is_passed($value, $rule_values[1]) == false) {
			        
		        	return true;
		        }
		
		        /*
				 * Define message for "date_between" rule.
				 * Example:
				 *
				 * Rule defined as: 'one_of' =>  array('2011-09-22', '2017-04-12')
				 * Will display message as: Please enter date between 2011-09-22 - 2017-04-12.
				 */
		        $rule_values = implode(' - ', $rule_values);
	        	
	        	break;
	
	        // Number
	        case 'number':
		
		        if(is_numeric($value)) {
			        return true;
		        }
		        
		        break;
	
	        // Digits
	        case 'digits':
		
		        if($this->lib->valid->digits($value) == true) {
			        return true;
		        }
		        
		        break;
			
		    // Id
	        case 'id':
		
		        if($this->lib->valid->id($value) == true) {
			        return true;
		        }
		
		        break;
	        	
	        // Alpha
	        case 'alpha':
		
		        if($this->lib->valid->alpha($value) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Alpha-numeric
	        case 'alphanumeric':
		
		        if($this->lib->valid->alpha_numeric($value)) {
			        return true;
		        }
		        
		        break;
	
	        // E-mail
	        case 'email':
		
		        if($this->lib->valid->email($value) and $value != '') {
			        return true;
		        }
		        
		        break;
	
	        // Credit card
	        // @deprecated
	        case 'creditcard':
	        case 'credit_card':
		        if($this->lib->valid->credit_card($value) == true) {
			        return true;
		        }
		        
		        break;
	
		    // Phone number
	        case 'phone_number':

		        if(in_array(trim(strtr($value, '0123456789', '##########')), $rule_values)) {
		        	return true;
		        }
		        
	        	break;
		        
	        // URL
	        case 'url':
		
		        if($this->lib->valid->url($value) == true) {
			        return true;
		        }
		        
		        break;
	
	        // Regular expression
	        case 'regex':
		
		        if(preg_match($rule_values, $value) == 1) {
			        return true;
		        }
		        
		        break;
			
		    // This rule can be enabled only with multiple rule.
			case 'unique':
				trigger_error('Rule `'.$rule.'` is not allowed for non multiple values!', E_USER_WARNING);
				return false;
			
	        default:
		
		        trigger_error('Rule `'.$rule.'` not exists!', E_USER_WARNING);
		        return false;
        }

        if(isset($this->rule_custom_messages[$element][$rule])) {
            // Message set by custom messages array.
            // NOTICE: The message should be defined as is, without translation.
            $this->set_message($element, $this->rule_custom_messages[$element][$rule]);
        } else {
            // Get message from default.
            $this->set_message($element, $this->message_localized($rule, $rule_values));
        }
	    
		return false;
    } // End func validate_rule

	/**
	 * Validate element with multiple values and set message
	 *
     * @access protected
     * @param string $element
     * @param mixed $value
     * @param string $rule
     * @param mixed $rule_values
     * @return boolean
     * @since 2.0.0
	 */
	protected function validate_multiple_rule($element, $value, $rule, $rule_values) {
	
		if(!is_array($value)) {
			if($rule != 'multiple' or $rule_values == false) {
				return true;
			} else {
				if(isset($this->rule_custom_messages[$element][$rule])) {
					$this->set_message($element, $this->rule_custom_messages[$element][$rule]);
				} else {
					$this->set_message($element, $this->message_localized($rule.'_non_range', $rule_values));
				}
				return false;
			}
		}
		
		if($rule == 'required') {
			
			if($rule_values == false) {
				return true;
			}
			
			foreach($value as $v) {
				if(!$this->lib->valid->required($v)) {
					if(isset($this->rule_custom_messages[$element][$rule])) {
						$this->set_message($element, $this->rule_custom_messages[$element][$rule]);
					} else {
						$this->set_message($element, $this->message_localized($rule, $rule_values));
					}
					return false;
				}
			}
			return true;
		}
		
		// remove empty values
		$value = array_filter($value, function($v) {
			return (trim($v) !== '');
		});
		
		switch($rule) {
			case 'multiple':
				if(!is_array($rule_values) || (count($value) >= $rule_values[0] && count($value) <= $rule_values[1])) {
					return true;
				}
				break;
			case 'unique':
				if($value === array_unique($value)) {
					return true;
				}
				break;
			default:
				foreach($value as $v) {
					if(!$this->validate_rule($element, $v, $rule, $rule_values)) {
						return false;
					}
				}
				return true;
		}
		
		if(isset($this->rule_custom_messages[$element][$rule])) {
			$this->set_message($element, $this->rule_custom_messages[$element][$rule]);
		} else {
			if($rule_values === true) {
				$this->set_message($element, $this->message_localized($rule));
			} else {
				$this->set_message($element, $this->message_localized($rule, $rule_values));
			}
		}
		
		return false;
		
	} // end func validate_multiple_rule
	
    /**
     * Return translated message
     *
     * @access protected
     * @param string $rule
     * @param mixed $second_args
     * @return string
     */
    protected function message_localized($rule, $second_args = array()) {
    	
    	if(!is_array($second_args)) {
    		$second_args = array($second_args);
    	}

        return $this->app->language('_' . $rule, $second_args);

    } // end func message_localized
	
	/**
	 * This is the wrapper method to get value for validation.
	 * It should be possible to validate any type of data such as:
	 *
	 * User defined array,
	 * Global arrays,
	 * - POST
	 * - PUT
	 * - DELETE
	 *
	 * @access protected
	 * @param string $item
	 * @return mixed
	 * @since Version 2.0.0
	 */
	protected function get_value($item) {
		
		switch($this->validation_data) {
			case 'POST':
				$value = $this->lib->filter->post($item);
				break;
			case 'PUT':
				$value = $this->lib->filter->put($item);
				break;
			case 'DELETE':
				$value = $this->lib->filter->delete($item);
				break;
			default:
				if(isset($this->validation_data[$item])) {
					$value = $this->validation_data[$item];
				} else {
					$value = false;
				}
		}
				
		return $value;
		
	} // End func get_value
	
} /* End of class form_validation_lib */