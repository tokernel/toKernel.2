<?php
/**
 * toKernel- Universal PHP Framework.
 * SMTP - Email sender class library.
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
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * smtp_lib class
 *
 * @author Arshak Gh. <khazaryan@gmail.com>
 * @author David A. <tokernel@gmail.com>
 */
class smtp_lib extends email_base_lib {

	/**
	 * Set recipients
	 *
	 * @access public
	 * @param mixed string | array $recipients
	 * @return void
	 */
	public function to($recipients) {

		if (!is_array($recipients)) {
			settype($recipients, 'array');
		}

		$this->set_email_header('To', implode(", ", $recipients));
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
		$this->cc_recipients = $cc_recipients;

		$this->debug_log('Set CC: ' . implode(', ', $this->cc_recipients));

	} // End func cc

	/**
	 * Send Email
	 *
	 * @access public
	 * @return bool
	 */
	public function send() {

		$this->debug_log('Starting send functionality');

		// Before trying to send we should validate all values.
		if(!$this->validate()) {
			return false;
		}

		$this->set_email_header('User-Agent', 'toKernel');
		$this->set_email_header('Date', $this->config['message_date']);

		if($this->reply_to) {
			$this->set_email_header('Reply-To', $this->reply_to);
		}

		$this->final_headers();
		$this->set_headers();

		$hdr = '';
		$body = '';

		$content_type = $this->content_type();

		$this->debug_log('Preparing type as: ' . $content_type);

		switch ($content_type) {

			case 'plain':

				$hdr .= "Content-Type: text/plain; charset=" . $this->config['charset'].$this->config['new_line'];
				$hdr .= "Content-Transfer-Encoding: 8bit";
				$this->config['final_body'] = $hdr.$this->config['new_line'].$this->config['new_line'].$this->config['body'];

				break;

			case 'html':

				if ($this->config['send_multipart'] === false) {
					$hdr .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['new_line'];
					$hdr .= "Content-Transfer-Encoding: quoted-printable";
				} else {

					$hdr .= "Content-Type: multipart/alternative; boundary=\"" .$this->config['alt_boundary']. "\"" .
							$this->config['new_line'].$this->config['new_line'];

					$body .= $this->config['mime_message'].$this->config['new_line'].$this->config['new_line'];
					$body .= "--" . $this->config['alt_boundary'].$this->config['new_line'];

					$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['new_line'];
					$body .= "Content-Transfer-Encoding: 8bit".$this->config['new_line'].$this->config['new_line'];
					$body .= $this->config['new_line'].$this->config['new_line']."--".$this->config['alt_boundary'].
							$this->config['new_line'];

					$body .= "Content-Type: text/html; charset=".$this->config['charset'].$this->config['new_line'];
					$body .= "Content-Transfer-Encoding: quoted-printable".$this->config['new_line'].$this->config['new_line'];
				}

				$this->config['final_body'] = $body.$this->q_printable($this->config['body']).$this->config['new_line'].
						$this->config['new_line'];

				$this->config['final_body'] = $hdr.$this->config['final_body'];

				if ($this->config['send_multipart'] !== false) {
					$this->config['final_body'].= "--".$this->config['alt_boundary']."--";
				}

				break;

			case 'plain-attach':

				$hdr .= "Content-Type: multipart/".$this->config['multipart']."; boundary=\"".
						$this->config['atc_boundary']."\"".$this->config['new_line'].$this->config['new_line'];

				$body .= $this->config['mime_message'].$this->config['new_line'].$this->config['new_line'];
				$body .= "--" . $this->config['atc_boundary'].$this->config['new_line'];
				$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['new_line'];
				$body .= "Content-Transfer-Encoding: 8bit ".$this->config['new_line'].$this->config['new_line'];
				$body .= $this->config['body'].$this->config['new_line'].$this->config['new_line'];

				break;

			case 'html-attach':

				$hdr .= "Content-Type: multipart/".$this->config['multipart']."; boundary=\"".
						$this->config['atc_boundary']."\"" . $this->config['new_line'].$this->config['new_line'];

				$body .= $this->config['mime_message'].$this->config['new_line'].$this->config['new_line'];
				$body .= "--" . $this->config['atc_boundary'].$this->config['new_line'];
				$body .= "Content-Type: multipart/alternative; boundary=\"".$this->config['alt_boundary'].
						"\"".$this->config['new_line'].$this->config['new_line'];

				$body .= "--" . $this->config['alt_boundary'].$this->config['new_line'];

				$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['new_line'];
				$body .= "Content-Transfer-Encoding: 8bit".$this->config['new_line'].$this->config['new_line'];
				$body .= $this->config['body'].$this->config['new_line'].$this->config['new_line']. "--" .
						$this->config['alt_boundary'].$this->config['new_line'];

				$body .= "Content-Type: text/html; charset=".$this->config['charset'].$this->config['new_line'];
				$body .= "Content-Transfer-Encoding: quoted-printable" . $this->config['new_line'].$this->config['new_line'];

				$body .= $this->q_printable($this->config['body']).$this->config['new_line'].$this->config['new_line'];
				$body .= "--" . $this->config['alt_boundary']."--".$this->config['new_line'].$this->config['new_line'];

				break;
		}

		if (count($this->file_attachments) > 0) {

			$this->debug_log('Preparing file attachments - count: ' . count($this->file_attachments));

			$attachment = array();
			foreach ($this->file_attachments as $val) {

				$h  = "--".$this->config['atc_boundary'].$this->config['new_line'];
				$h .= "Content-type: ".$val['type']."; ";
				$h .= "name=\"".basename($val['filename'])."\"".$this->config['new_line'];
				$h .= "Content-Disposition: ".$val['disp'].";".$this->config['new_line'];
				$h .= "Content-Transfer-Encoding: base64".$this->config['new_line'];

				$attachment[] = $h;

				$fp = fopen($val['filename'], 'r');
				$attachment[] = chunk_split(base64_encode(fread($fp, (filesize($val['filename']) +1))));
				fclose($fp);
			}

			$body.= implode($this->config['new_line'], $attachment).$this->config['new_line']."--".
					$this->config['atc_boundary']."--";

			$this->config['final_body'] = $hdr.$body;
		}

		if(!$this->send_final()) {
			$this->error[] = 'Failed to send email';
			$this->debug_log('Failed to send email');
			return false;
		} else {
			$this->debug_log('Email Sent successfully!');
			return true;
		}

	} // End func send

	/**
	 * Final Sending after preparation
	 *
	 * @access protected
	 * @return bool
	 */
	protected function send_final() {

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if ($socket < 0) {
			$message = 'SMTP: socket_create() failed: '.socket_strerror(socket_last_error());
			$this->error[] = $message;
			$this->debug_log($message);
			return false;
		}

		$result = socket_connect($socket, $this->config['smtp_host'], $this->config['smtp_port']);

		if ($result === false) {
			$message = 'SMTP: socket_connect() failed: '.socket_strerror(socket_last_error());
			$this->error[] = $message;
			$this->debug_log($message);
			return false;
		}

		if(!is_resource($socket)) {
			$message = 'SMTP: $socket is not resource.';
			$this->error[] = $message;
			$this->debug_log($message);
			return false;
		}

		$this->debug_log('SMTP: Read information from SMTP Server');
		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Send Hello to SMTP Server');
		if(!$this->write_to_smtp($socket, 'EHLO '.$this->config['smtp_user'])) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Response received');

		$this->debug_log('SMTP: Authentication on server');
		if(!$this->write_to_smtp($socket, 'AUTH LOGIN')) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		if(!$this->write_to_smtp($socket, base64_encode($this->config['smtp_user']))) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		if(!$this->write_to_smtp($socket, base64_encode($this->config['smtp_password']))) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Authentication done successfully');
		$this->debug_log('SMTP: Setting sender address');

		if(!$this->write_to_smtp($socket, 'MAIL FROM:<'.$this->only_email($this->config['email_headers']['From']).'>')) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Setting recipients address');

		foreach ($this->recipients as $val) {
			if ($val != '') {
				if(!$this->write_to_smtp($socket, 'RCPT TO:<'.$val.'>')) {
					return false;
				}
			}
		}

		if (is_array($this->cc_recipients)) {

			$this->debug_log('SMTP: Setting CC recipients');

			foreach ($this->cc_recipients as $val) {
				if ($val != '') {
					if(!$this->write_to_smtp($socket, 'RCPT TO:<'.$val.'>')) {
						return false;
					}
				}
			}

		}

		if (is_array($this->bcc_recipients)) {

			$this->debug_log('SMTP: Setting BCC recipients');

			foreach ($this->bcc_recipients as $val) {
				if ($val != '') {
					if(!$this->write_to_smtp($socket, 'RCPT TO:<'.$val.'>')) {
						return false;
					}
				}
			}

		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Setting Mail Body');

		if(!$this->write_to_smtp($socket, 'DATA')) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		if(!$this->write_to_smtp(
			$socket,
			$this->config['email_header'] . preg_replace('/^\./m', '..$1', $this->config['final_body']) . $this->config['crlf'] . "." . $this->config['crlf']
		)) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		$this->debug_log('SMTP: Mail Body write successfully');
		$this->debug_log('SMTP: Closing connection');

		if(!$this->write_to_smtp($socket, 'QUIT')) {
			return false;
		}

		if(!$this->read_from_smtp($socket)) {
			return false;
		}

		return true;

	} // End func send_final


	/**
	 * Read response data from SMTP
	 *
	 * @access	protected
	 * @param	resource
	 * @return	string
	 */
	protected function read_from_smtp($socket) {

		$read = socket_read($socket, 1024);

		if ($read{0} != '2' and $read{0} != '3') {
			$message = 'SMTP: socket read : ' . socket_strerror(socket_last_error());
			$this->error[] = $message;
			$this->debug_log($message);
			return false;
		} else {
			return true;
		}

	} // End func read_from_smtp

	/**
	 * Write data to SMTP
	 *
	 * @access	protected
	 * @param	resource
	 * @param	string
	 * @return	bool
	 */
	protected function write_to_smtp($socket, $data) {

		$data = $data . $this->config['new_line'];
		$write = socket_write($socket, $data, strlen($data));

		if ($write === false) {
			$message = 'SMTP: socket write : ' . socket_strerror(socket_last_error());
			$this->error[] = $message;
			$this->debug_log($message);
			return false;
		} else {
			return true;
		}

	} // End func write_to_smtp

} // End class smtp_lib
?>