<?php
/**
 * toKernel - Universal PHP Framework.
 * Hooks base class library
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
 * @category   kernel
 * @package    framework
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * hooks_base class
 *
 * @author David A. <tokernel@gmail.com>
 */
class hooks_base {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Main Addons object for accessing all addons
 *
 * @var object
 * @access protected
 */
 protected $addons;

/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;
   	
/**
 * Status of hooks loaded
 * 
 * @access private
 * @staticvar bool
 */	
 private static $_loaded = false;
	
/**
 * Class constructor
 * 
 * @access public
 */
 public function __construct() {

 	/* Check, is hooks is already loaded */
	if(self::$_loaded == true) {
		trigger_error('Hooks is already loaded in app::run().', E_USER_ERROR);
	}
		
	$this->lib = lib::instance();
	$this->app = app::instance();
	$this->addons = addons::instance();

	self::$_loaded = true;
	
 } // end constructor
	
/* End of class hooks */
}

/* End of file */
?>