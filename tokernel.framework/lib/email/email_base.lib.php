<?php
/**
 * toKernel- Universal PHP Framework.
 * Email sender base class library.
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * email_base_lib class
 *
 * @abstract
 * @author Arshak Gh. <khazaryan@gmail.com>
 * @author David A. <tokernel@gmail.com>
 */
abstract class email_base_lib {

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @access protected
	 * @var object
	 */
	protected $lib;

	/**
	 * Email subject
	 *
	 * @access protected
	 * @var string
	 */
	protected $subject;

	/**
	 * Recipients
	 *
	 * @access protected
	 * @var array
	 */
	protected $recipients = array();

	/**
	 * cc Recipients
	 *
	 * @access protected
	 * @var array
	 */
	protected $cc_recipients = array();

	/**
	 * bcc Recipients
	 *
	 * @access protected
	 * @var array
	 */
	protected $bcc_recipients = array();

	/**
	 * Reply to address
	 *
	 * @acess protected
	 * @var string
	 */
	protected $reply_to;

	/**
	 * From address
	 *
	 * @access protected
	 * @var string
	 */
	protected $from;

	/**
	 * File attachments
	 *
	 * @access protected
	 * @var array
	 */
	protected $file_attachments = array();

	/**
	 * HTML Message
	 *
	 * @access protected
	 * @var string
	 */
	protected $message_html;

	/**
	 * Text Message
	 *
	 * @access protected
	 * @var string
	 */
	protected $message_text;

	/**
	 * Array of Errors
	 *
	 * @access protected
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Configuration array
	 *
	 * @access protected
	 * @var array
	 */
	protected $config = array();

	/**
	 * MIME Types configuration ini file object
	 *
	 * @access protected
	 * @var object
	 */
	protected $mime_types;

	/**
	 * LOG Object instance for debug logging
	 *
	 * @access protected
	 * @var object
	 */
	protected $log;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($config) {

		$this->lib = lib::instance();

		$config['body'] = '';
		$config['email_header'] = '';
		$config['new_line'] = "\n";
		$config['crlf'] = "\n";
		$config['mail_type']  = 'text';
		$config['multipart']  =  'mixed';
		$config['message_date'] = date('D, d M Y H:i:s O');
		$config['alt_boundary'] = 'B_ALT_' . uniqid();
		$config['atc_boundary'] = 'B_ATC_' . uniqid();
		$config['mime_message'] = 'This is a multi-part message in MIME format.' . $config['new_line'] .
								  'Your email application may not support this format.';

		$this->config = $config;

		// Initialize log object
		$this->log = $this->lib->log->instance('debug_email_' . $this->config['section_name'] . '.log');

		$this->debug_log('===== Email library configuration instance ['.$this->config['section_name'].'] initialized =====');
		$this->debug_log('Protocol: ' . $this->config['protocol']);

	} // End constructor

	/**
	 * Write debug log if enabled
	 *
	 * @access protected
	 * @param string $message
	 * @return void
	 */
	protected function debug_log($message) {

		if(isset($this->config['debug_log']) and $this->config['debug_log'] == 1) {
			$this->log->write($message);
		}

	} // End func debug_log

	/**
	 * Set email subject
	 *
	 * @access public
	 * @param string $subject
	 * @return void
	 */
	public function subject($subject) {
		$this->subject = $subject;
		$this->set_email_header('Subject', $this->q_encode($subject));

		$this->debug_log('Set Subject: ' . $subject);
	}

	/**
	 * Set recipients
	 * This method can be overwritten by each class of protocol
	 *
	 * @access public
	 * @param mixed string | array $recipients
	 * @return void
	 */
	public function to($recipients) {

		if (!is_array($recipients)) {
			settype($recipients, 'array');
		}

		$this->recipients = $recipients;

		$this->debug_log('Set Recipients: ' . implode(', ', $this->recipients));

	} // End func to

	/**
	 * Set cc recipients
	 *
	 * @access public
	 * @param mixed string | array $cc_recipients
	 * @return void
	 */
	public function cc($cc_recipients) {

		if(!is_array($cc_recipients)) {
			settype($cc_recipients, 'array');
		}

		$this->set_email_header('Cc', implode(", ", $cc_recipients));

		$this->debug_log('Set CC: ' . implode(', ', $cc_recipients));

	} // End func cc

	/**
	 * Set bcc recipients
	 *
	 * @access public
	 * @param mixed string | array $bcc_recipients
	 * @return void
	 */
	public function bcc($bcc_recipients) {

		if(!is_array($bcc_recipients)) {
			settype($bcc_recipients, 'array');
		}

		$this->bcc_recipients = array_merge($this->bcc_recipients, $bcc_recipients);

		$this->debug_log('Set BCC: ' . implode(', ', $this->bcc_recipients));

	} // End func bcc

	/**
	 * Set Reply to address
	 *
	 * @access public
	 * @param string $reply_to
	 * @return void
	 */
	public function reply_to($reply_to) {
		$this->reply_to = $reply_to;
		$this->debug_log('Set Reply to: ' . $this->reply_to);
	}

	/**
	 * Set from address
	 *
	 * @access public
	 * @param string $from
	 * @param string $name = ''
	 * @return void
	 */
	public function from($from, $name = '') {

		$this->from = $from;

		$this->set_email_header('From', $this->q_encode($name).' <'.$from.'>');
		$this->set_email_header('Return-Path', '<'.$from.'>');

		$this->debug_log('Set From: ' . $this->from . ' ' . $name);

	} // End func from

	/**
	 * Attach file(s)
	 *
	 * @access public
	 * @param mixed array | string $attachment
	 * @param string $disposition = 'attachment'
	 * @return void
	 */
	public function attach_file($attachment, $disposition = 'attachment') {

		if(!is_array($attachment)) {
			settype($attachment, 'array');
		}

		foreach($attachment as $file) {

			$this->file_attachments[] = array(
				'filename' => $file,
				'type' => $this->get_mime_type(
						strtolower(substr($file, strrpos($file, '.')+1, strlen($file)))
				),
				'disp' => $disposition);

			$this->debug_log('Attache file: ' . $file);

		}



	} // End func attach_file

	/**
	 * Set HTML Message
	 *
	 * @access public
	 * @param string $message_html
	 * @return void
	 */
	public function message_html($message_html) {

		$this->message_html = $message_html;

		$this->config['mail_type'] = 'html';

		$this->message_text = $message_html;
		$this->config['body'] = stripslashes(rtrim(str_replace("\r", "", $message_html)));

		$this->debug_log('Set HTML Message - length: ' . strlen($this->message_html));

	} // End func message_html

	/**
	 * Set Text Message
	 *
	 * @access public
	 * @param string $message_text
	 * @return void
	 */
	public function message_text($message_text) {

		$this->message_text = $message_text;
		$this->config['body'] = stripslashes(rtrim(str_replace("\r", "", $message_text)));

		$this->debug_log('Set Text Message - length: ' . strlen($this->message_text));

	} // End func message_text

	/**
	 * Reset all values
	 *
	 * @access public
	 * @return void
	 */
	public function reset() {

		$this->subject = '';
		$this->from = '';
		$this->recipients = array();
		$this->cc_recipients = array();
		$this->bcc_recipients = array();
		$this->reply_to = '';
		$this->file_attachments = array();
		$this->message_html = '';
		$this->message_text = '';

		$this->debug_log('Rest done.');

	} // End func reset

	/**
	 * Validate email before send
	 *
	 * @access public
	 * @return bool
	 */
	public function validate() {

		$this->debug_log('Validation start');

		// Checking required values
		if($this->subject == '') {
			$this->errors[] = 'Empty `subject`';
			$this->debug_log('Validation: Empty `subject`');
		}

		if($this->from == '') {
			$this->errors[] = 'Empty `from` address';
			$this->debug_log('Validation: Empty `from` address');
		}

		if(empty($this->recipients)) {
			$this->errors[] = 'Empty `recipients` address';
			$this->debug_log('Validation: Empty `recipients` address');
		}

		if($this->message_html == '' and $this->message_text == '') {
			$this->errors[] = 'Empty `message`';
			$this->debug_log('Validation: Empty `message`');
		}

		// Optional validation
		if(!empty($this->file_attachments)) {
			foreach($this->file_attachments as $file) {
				if(!is_file($file['filename']) or !is_readable($file['filename'])) {
					$this->errors[] = 'Attachment file `'.$file['filename'].'` doesn\'t exists or not readable';
					$this->debug_log('Validation: Attachment file `'.$file['filename'].'` doesn\'t exists or not readable');
				}
			}
		}

		// Return result
		if(!empty($this->errors)) {
			$this->debug_log('Validation not passed');
			return false;
		}

		$this->debug_log('Validation passed successfully');
		return true;

	} // End func validate

	/**
	 * Return Errors array
	 *
	 * @access public
	 * @return array
	 */
	public function errors() {
		return $this->errors;
	}

	/**
	 * Do Q encode text
	 *
	 * @access	protected
	 * @param	string $str
	 * @return	string
	 */
	protected function q_encode($str) {

		if (!$str) {
			return false;
		}

		$str = str_replace(array("\r", "\n"), array('', ''), $str);
		$limit = 75 - 7 - strlen($this->config['charset']);

		$convert = array('_', '=', '?');

		$output = '';
		$temp = '';

		for ($i = 0, $length = strlen($str); $i < $length; $i++) {

			$char = substr($str, $i, 1);
			$ascii = ord($char);

			if ($ascii < 32 or $ascii > 126 or in_array($char, $convert)) {
				$char = '='.dechex($ascii);
			}

			if ($ascii == 32) {
				$char = '_';
			}

			if ((strlen($temp) + strlen($char)) >= $limit) {
				$output .= $temp.$this->config['crlf'];
				$temp = '';
			}

			$temp .= $char;
		}

		$str = $output.$temp;

		return trim(preg_replace('/^(.*)$/m', ' =?'.$this->config['charset'].'?Q?$1?=', $str));

	} // End of function q_encode

	/**
	 * Prepare string for Quoted-Printable Content-Transfer-Encoding
	 *
	 * @access	protected
	 * @param string $str
	 * @param integer $charlim = 0
	 * @return string
	 */
	protected function q_printable($str, $charlim = 0) {

		if (!$str) {
			return false;
		}

		if (!$charlim or $charlim > 76) {
			$charlim = 76;
		}

		$str = preg_replace("| +|", ' ', $str);
		$str = preg_replace('/\x00+/', '', $str);

		if (strpos($str, "\r") !== FALSE) {
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		$lines = explode("\n", $str);
		$escape = '=';
		$output = '';

		foreach ($lines as $line) {

			$length = strlen($line);
			$temp = '';

			for ($i = 0; $i < $length; $i++) {

				$char = substr($line, $i, 1);
				$ascii = ord($char);

				if ($i == ($length - 1)) {
					$char = ($ascii == '32' or $ascii == '9') ? $escape.sprintf('%02s', dechex($ascii)) : $char;
				}

				if ($ascii == '61') {
					$char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));
				}

				if ((strlen($temp) + strlen($char)) >= $charlim) {
					$output .= $temp.$escape.$this->config['crlf'];
					$temp = '';
				}

				$temp .= $char;
			}

			$output .= $temp.$this->config['crlf'];
		}

		$output = substr($output, 0, strlen($this->config['crlf']) * -1);

		return $output;

	} // End of function q_printable

	/**
	 * Return message ID
	 *
	 * @access protected
	 * @return string
	 */
	protected function message_id() {
		return  "<" . uniqid('') . strstr($this->config['email_headers']['Return-Path'], '@');
	} // End of function message_id

	/**
	 * Build final headers
	 *
	 * @access protected
	 * @return mixed
	 */
	protected function final_headers() {

		$this->debug_log('Setting final headers');

		if (!isset($this->config['email_headers']['From'])) {
			$this->debug_log('Final headers setting fail: email_headers/from id empty.');
			return false;
		}

		$this->set_email_header('X-Sender', $this->config['email_headers']['From']);
		$this->set_email_header('X-Mailer', 'toKernel');
		$this->set_email_header('X-Priority', $this->config['priority']);
		$this->set_email_header('Message-ID', $this->message_id());
		$this->set_email_header('Mime-Version', '1.0');

		$this->debug_log('Final headers setting done');

	} // End func final_headers

	/**
	 * Return content type (text | html | attachment)
	 *
	 * @access protected
	 * @return string
	 */
	protected function content_type() {

		if($this->config['mail_type'] == 'html' and count($this->file_attachments) == 0) {
			return 'html';
		} elseif($this->config['mail_type'] == 'html' and count($this->file_attachments) > 0) {
			return 'html-attach';
		} elseif($this->config['mail_type'] == 'text' and count($this->file_attachments) > 0) {
			return 'plain-attach';
		} else {
			return 'plain';
		}

	} // End of function content_type

	/**
	 * Add Header
	 *
	 * @access protected
	 * @param string $header
	 * @param string $value
	 * @return void
	 */
	protected function set_email_header($header, $value) {
		$this->config['email_headers'][$header] = $value;
	}

	/**
	 * Get Mime Type
	 *
	 * @access	protected
	 * @param string
	 * @return string
	 */
	protected function get_mime_type($ext = '') {

		$mime = '';

		if($ext != '') {

			$this->load_mime_types_ini();
			$mime = $this->mime_types->item_get($ext);
		}

		if($mime == '') {
			$mime = 'application/x-unknown-content-type';
		}

		return $mime;

	} // End of function get_mime_type

	/**
	 * Load MIME Types configuration ini file object
	 *
	 * @access protected
	 * @return mixed
	 */
	protected function load_mime_types_ini() {

		// Object is already loaded
		if(is_object($this->mime_types)) {
			return true;
		}

		$ini_file = TK_PATH . 'config' . TK_DS . 'mimes.ini';

		$this->debug_log('Loading mime types configuration file: ' . $ini_file);

		if(!is_readable($ini_file)) {
			$this->debug_log('Mime types configuration file is not readable');
			trigger_error('Cannot load file: ' . $ini_file, E_USER_ERROR);
		}

		$this->mime_types = $this->lib->ini->instance($ini_file);
		$this->debug_log('Mime types configuration file loaded');

	} // End func load_mime_types_ini

	/**
	 * Combine headers as a string
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function set_headers() {

		$this->debug_log('Setting headers');

		foreach($this->config['email_headers'] as $key => $val) {
			if (trim($val) != '') {
				$this->config['email_header'] .= $key . ": " . $val . $this->config['new_line'];
			}
		}

	} // End of function set_headers

	/**
	 * Parse and return email address from string
	 *
	 * @access protected
	 * @param string $email
	 * @return string
	 */
	protected function only_email($email) {

		if(!is_array($email)) {
			if (preg_match('/\<(.*)\>/', $email, $match)) {
				return $match['1'];
			} else {
				return $email;
			}
		}

		$clean_email = array();

		foreach ($email as $addy) {
			if (preg_match( '/\<(.*)\>/', $addy, $match)) {
				$clean_email[] = $match['1'];
			} else {
				$clean_email[] = $addy;
			}
		}

		return $clean_email;

	} // End func only_email

	/**
	 * Abstract function send()
	 *
	 * @abstract
	 * @access public
	 * @return bool
	 */
	abstract public function send();

} /* End of class email_base_lib */