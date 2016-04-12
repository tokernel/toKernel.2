<?php
/**
 * toKernel- Universal PHP Framework.
 * SENDMAIL - Email sender class library.
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
 * sendmail_lib class
 *
 * @author Arshak Gh. <khazaryan@gmail.com>
 * @author David A. <tokernel@gmail.com>
 */
class sendmail_lib extends email_base_lib {

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
		$this->recipients = implode(", ", $recipients);

		$this->debug_log('Set Recipients: ' . $this->recipients);

	} // End func to

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
		$this->set_email_header('Bcc', implode(", ", $this->bcc_recipients));

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

		$fp = popen($this->config['sendmail_path'] . " -oi -f " .
				$this->only_email($this->config['email_headers']['From'])." -t", 'w');

		fputs($fp, $this->config['email_header']);
		fputs($fp, $this->config['final_body']);

		$status = pclose($fp);

		if ($status != 0) {
			$this->debug_log('Failed to send mail. Status: ' . $status);
			return false;
		}

		$this->debug_log('Email Sent successfully!');
		return true;

	} // End func send

} // End class sendmail_lib
?>