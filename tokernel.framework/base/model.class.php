<?php
/**
 * toKernel - Universal PHP Framework.
 * Base model class for models.
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
 * @category   base
 * @package    framework
 * @subpackage base
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * class model
 *
 * @author David A. <tokernel@gmail.com>
 */
class model {

	/**
	 * Status of model
	 *
	 * @access protected
	 * @staticvar bool
	 */
	protected static $initialized;

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;

	/**
	 * Database library object
	 *
	 * @access protected
	 * @var object
	 */
	protected $db;

	/**
	 * Log object
	 *
	 * @access protected
	 * @var object
	 */
	protected $log;

	/**
	 * Current language prefix of application
	 * Example url request: http://example.com/fr/mycontroller/myaction
	 * Result of value: fr
	 *
	 * @var string
	 */
	protected $language_prefix;

	/**
	 * ID of model
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * ID of this model owner addon
	 *
	 * @var string
	 */
	protected $id_addon;

	/**
	 * Class Constructor
	 *
	 * @access public
	 * @param array $params
	 * @return void
	 */
	public function __construct($params) {

		// Define main library
		$this->lib = lib::instance();

		// Define model log object
		$this->log = $params['~log'];

		// Define language prefix
		$this->language_prefix = $params['~language_prefix'];

		// Define Model, Addon id
		$this->id = $params['~id'];
		$this->id_addon = $params['~id_addon'];

		// Define Database object
		$this->db = $this->lib->db->instance($params['~instance']);

		// Initialized
		self::$initialized = true;

		// Unset temporary parameters
		unset($params);

	} // End func __construct

	/* End of class model */
}

/* End of file */
?>