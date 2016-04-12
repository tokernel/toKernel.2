<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for compressing content, such as javascript and css
 *
 * - Remove comments
 * - Remove more than one whitespaces
 * - Remove tabs, new lines
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
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.6.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * compress_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class compress_lib {

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;

	/**
	 * File extension allowed to compress
	 *
	 * @access protected
	 * @var array
	 */
	protected $file_types = array('js', 'css');

	/**
     * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function  __construct() {
		$this->lib = lib::instance();
	}

	/**
	 * Compress javascript content
	 *
	 * @access public
	 * @param string $buffer
	 * @return string
	 */
	public function javascript($buffer) {

		/* remove comments */
		$buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $buffer);

		/* remove tabs, spaces, new lines, etc. */
		$buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);

		/* remove other spaces before/after ) */
		$buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);

		$buffer = trim($buffer);
		$buffer = rtrim($buffer, ';') . ';';

		return $buffer;

	} // End func javascript

	/**
	 * Compress css content
	 *
	 * @access public
	 * @param string $buffer
	 * @return string
	 */
	public function css($buffer) {

		/* remove comments */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

		/* remove tabs, spaces, new lines, etc. */
		$buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);

		/* remove other spaces before/after ; */
		$buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
		$buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
		$buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);

		return $buffer;

	} // End func css

	/**
	 * Compress file
	 * Type will detected automatically
	 * Compress and save file if destination file set.
	 * Return compressed file content if destination file not set.
	 *
	 * @access public
	 * @param string $source_file
	 * @param mixed $destination_file
	 * @return mixed
	 */
	public function file($source_file, $destination_file = NULL) {

		// Detect type
		$type = $this->lib->file->ext($source_file);

		// Check if type allowed
		if(!in_array($type, $this->file_types)) {
			trigger_error('Invalid file type: ' . $type, E_USER_ERROR);
		}

		// Check file
		if(!is_readable($source_file) or !is_file($source_file)) {
			trigger_error("File: " . $source_file . " doesn't exists!", E_USER_ERROR);
		}

		// Load content
		$content = $this->lib->file->read($source_file);

		// Compress by type
		if($type == 'js') {
			$content = $this->javascript($content);
		}

		if($type == 'css') {
			$content = $this->css($content);
		}

		// Save to file if specified
		if(!is_null($destination_file)) {
			$this->lib->file->write($destination_file, $content);
			return true;
		}

		// Return content
		return $content;

	} // End func file

	/**
	 * Build combined files content from batch.
	 * Save combined content to destination file if specified.
	 * Return combined content if destination file not specified.
	 *
	 * $source_files associative array should be defined as:
	 *
	 * array(
	 *     'filename1.js' => true // means compress, than combine
	 *     'filename1.js' => false // means just combine the file without compression
	 * )
	 *
	 * @access public
	 * @param array $source_files
	 * @param mixed $destination_file = NULL
	 * @return mixed
	 */
	public function files($source_files, $destination_file = NULL) {

		// Check if array not empty
		if(empty($source_files)) {
			trigger_error("Empty files list!", E_USER_ERROR);
		}

		$content = '';

		// Build/combine content with all files
		foreach($source_files as $file => $do_compress) {

			if($do_compress == true) {
				$content .= $this->file($file);
			} else {
				$content .= $this->lib->file->read($file);
			}

		} // End foreach

		// Return content, if destination file not specified
		if(is_null($destination_file)) {
			return $content;
		}

		// Save file
		$this->lib->file->write($destination_file, $content);

		// Return file name
		return $destination_file;

	} // End func files

} // End class compress_lib

// End of file
?>
