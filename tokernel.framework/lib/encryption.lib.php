<?php
/**
 * toKernel - Universal PHP Framework.
 *
 * Simple encryption library implementation of AES-256 encryption in CBC mode
 * that uses PBKDF2 to create encryption key out of plain-text password
 * and HMAC to authenticate the encrypted message.
 *
 * PBKDF2 is used for creation of encryption key.
 * HMAC is used to authenticate the encrypted message.
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
 * encryption_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class encryption_lib {

	/**
	 * Algorithm
	 *
	 * @access protected
	 * @var string
	 */
	protected $pbkdf2_hash_algorithm = 'SHA256';

	/**
	 * Iterations
	 *
	 * @access protected
	 * @var string
	 */
	protected $pbkdf2_iterations = 64000;

	/**
	 * Salt byte size
	 *
	 * @access protected
	 * @var string
	 */
	protected $pbkdf2_salt_byte_size = 32;

	/**
	 * Hash byte size
	 *
	 * @access protected
	 * @var string
	 */
	protected $pbkdf2_hash_byte_size = 32;

	/**
	 * Encryption password
	 * Set by default
	 *
	 * @access protected
	 * @var string
	 */
	protected $password;

	/**
	 * Secure encryption key
	 *
	 * @access protected
	 * @var string
	 */
	protected $secure_encryption_key;

	/**
	 * Secure HMAC key
	 *
	 * @access protected
	 * @var string
	 */
	protected $secure_hmac_key;

	/**
	 * PBKDF2 salt
	 *
	 * @access protected
	 * @var string
	 */
	protected $pbkdf2_salt;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->password = '1B04Zrtuob2WfE66fCOY3xdti23ErWRrQXV2eEZ3Ak';
	}

	/**
	 * Return instance of this object
	 * If password is null the default password will used.
	 *
	 * @access public
	 * @param string $password = ''
	 * @return object
	 */
	public function instance($password = '') {

		$obj = clone $this;

		// Set password if specified
		if($password != '') {
			$obj->set_password($password);

		// If password not specified reset object to use default password
		} else {
			$obj->__construct();
		}

		return $obj;

	} // End func instance

	/**
	 * Set password
	 *
	 * @access public
	 * @param string $password
	 * @return void
	 */
	public function set_password($password) {
		$this->password = $password;
	}

	/**
	 * Encrypts content
	 * Return format: hmac:pbkdf2Salt:iv:encryptedText
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function encrypt($content) {

		$this->derivative_secure_keys();

		$mcryptIvSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

		/*
		 * By default mcrypt_create_iv() function uses /dev/random as a source of random values.
		 * For some performance issues it is now uses /dev/urandom
		 */
		$iv = mcrypt_create_iv($mcryptIvSize, MCRYPT_DEV_URANDOM);

		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->secure_encryption_key, $content, MCRYPT_MODE_CBC, $iv);

		$hmac = $this->hmac($this->pbkdf2_salt . $iv . $encrypted);

		return implode(':', array(
			base64_encode($hmac),
			base64_encode($this->pbkdf2_salt),
			base64_encode($iv),
			base64_encode($encrypted)
		));

	} // End func encrypt

	/**
	 * Decrypt encrypted content
	 * Encrypted content format: hmac:pbkdf2Salt:iv:encryptedText
	 *
	 * @access public
	 * @param string $enc_content
	 * @return string
	 */
	public function decrypt($enc_content) {

		$parts = explode(':', $enc_content);

		if(count($parts) < 4) {
			trigger_error('Invalid Encrypted content.', E_USER_ERROR);
		}

		$hmac = $parts[0];
		$pbkdf2_salt = $parts[1];
		$iv = $parts[2];
		$encrypted = $parts[3];

		$hmac = base64_decode($hmac);
		$pbkdf2_salt = base64_decode($pbkdf2_salt);
		$iv = base64_decode($iv);
		$encrypted = base64_decode($encrypted);

		$this->derivative_secure_keys($pbkdf2_salt);

		$calculated_hmac = $this->hmac($pbkdf2_salt . $iv . $encrypted);

		if (!$this->is_equal_hashes($calculated_hmac, $hmac)) {
			trigger_error('Invalid HMAC.', E_USER_ERROR);
		}

		return rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128, $this->secure_encryption_key, $encrypted, MCRYPT_MODE_CBC, $iv
			),
			"\0"
		);

	} // End func decrypt

	/**
	 * Compare two strings
	 *
	 * @access protected
	 * @param string $known_hash
	 * @param string $user_hash
	 * @return bool
	 */
	protected function is_equal_hashes($known_hash, $user_hash) {

		if (function_exists('hash_equals')) {
			return hash_equals($known_hash, $user_hash);
		}

		$knownLen = strlen($known_hash);
		$userLen = strlen($user_hash);

		if ($userLen !== $knownLen) {
			return false;
		}

		$result = 0;
		for ($i = 0; $i < $knownLen; $i++) {
			$result |= (ord($known_hash[$i]) ^ ord($user_hash[$i]));
		}

		/* They are only identical strings if $result is exactly 0 */
		return 0 === $result;

	} // End func is_equal_hashes

	/**
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 *
	 * @access protected
	 * @param string $algorithm
	 * @param string $password
	 * @param string $salt
	 * @param int $count
	 * @param int $key_length
	 * @param bool $raw_output
	 * @return string
	 */
	protected function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {

		$algorithm = strtolower($algorithm);

		if (!in_array($algorithm, hash_algos(), true)) {
			trigger_error('Invalid hash algorithm.', E_USER_ERROR);
		}

		if ($count <= 0 || $key_length <= 0) {
			trigger_error('Invalid parameters.', E_USER_ERROR);
		}

		if (function_exists('hash_pbkdf2')) {
			if (!$raw_output) {
				$key_length *= 2;
			}
			return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
		}

		$hash_length = strlen(hash($algorithm, '', true));
		$block_count = ceil($key_length / $hash_length);

		$output = '';

		for ($i = 1; $i <= $block_count; $i++) {

			$last = $salt . pack('N', $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);

			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if ($raw_output) {
			return substr($output, 0, $key_length);
		} else {
			return bin2hex(substr($output, 0, $key_length));
		}

	} // End func pbkdf2

	/**
	 * Create secure PBKDF2 derivatives out of the password.
	 *
	 * @access protected
	 * @param mixed $pbkdf2_salt = NULL
	 * @return void
	 */
	protected function derivative_secure_keys($pbkdf2_salt = null) {

		if ($pbkdf2_salt) {
			$this->pbkdf2_salt = $pbkdf2_salt;
		} else {
			$this->pbkdf2_salt = mcrypt_create_iv($this->pbkdf2_salt_byte_size, MCRYPT_DEV_URANDOM);
		}

		list($this->secure_encryption_key, $this->secure_hmac_key) = str_split(
			$this->pbkdf2($this->pbkdf2_hash_algorithm, $this->password, $this->pbkdf2_salt, $this->pbkdf2_iterations, $this->pbkdf2_hash_byte_size * 2, true),
			$this->pbkdf2_hash_byte_size
		);

	} // End func derivative_secure_keys

	/**
	 * Calculate HMAC
	 *
	 * @access protected
	 * @param string $content
	 * @return string
	 */
	protected function hmac($content) {
		return hash_hmac($this->pbkdf2_hash_algorithm, $content, $this->secure_hmac_key, true);
	}

// End class encryption_lib
}

// End of file
?>