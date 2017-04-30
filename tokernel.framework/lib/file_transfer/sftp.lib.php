<?php
/**
 * toKernel - Universal PHP Framework.
 * SFTP class library.
 *
 * NOTICE: The php-pecl-ssh2 extension need to be installed.
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
 * @category    library
 * @package     framework
 * @subpackage  library
 * @author      toKernel development team <framework@tokernel.com>
 * @copyright   Copyright (c) 2017 toKernel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version     1.0.0
 * @link        http://www.tokernel.com
 * @since       File available since Release 2.3.0
 * @uses        ssh2 (php-pecl-ssh2 extension)
 * @todo        Authenticate using a public key. See: ssh2_auth_pubkey_file().
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * sftp_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class sftp_lib extends file_transfer_base_lib {
	
	/**
	 * @var resource
	 * @access protected
	 */
	protected $sftp_res;
		
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config) {
		
		// Set Default configuration for FTP
		$this->config = array(
			'driver' => 'sftp',
			'host' => 'localhost',
			'port' => '22',
			'use_ssl' => 0,
			'username' => '',
			'password' => '',
			'log_errors' => 1,
			'display_errors' => 1
		);
		
		parent::__construct($config);
		
	}
	
	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
				
		parent::__destruct();
		
		unset($this->sftp_res);
	}
	
	/**
	 * Connect to SFTP Server
	 *
	 * By default, the connection credentials defined in configuration file.
	 * In case if you don't want to put your data into /disk/file for security purpose,
	 * you can leave them as empty and just call this method with arguments.
	 *
	 * @access public
	 * @param mixed $host
	 * @param mixed $port
	 * @param mixed $username
	 * @param mixed $password
	 * @return bool
	 */
	public function connect($host = NULL, $port = NULL, $username = NULL, $password = NULL) {
				
		// In case if setting connection values as arguments to this method.
		if(!is_null($host) and !is_null($port)) {
			$this->config('host', $host);
			$this->config('port', $port);
		}
		
		if(!is_null($username) and !is_null($password)) {
			$this->config('username', $username);
			$this->config('password', $password);
		}
				
		try {
			
			// Check if PHP Extension available.
			if (!function_exists('ssh2_connect')) {
				throw new Exception('php-pecl-ssh2 extension need to be installed!');
			}
			
			if (!$this->conn_res = ssh2_connect($this->config['host'], $this->config['port'])) {
				throw new Exception('Unable to connect SFTP host - ' . $this->config['host'] . ':' . $this->config['port']);
			}
			
			ssh2_auth_password($this->conn_res, $this->config['username'], $this->config['password']);
			
			if (!$this->sftp_res = ssh2_sftp($this->conn_res)) {
				throw new Exception('Could not initialize SFTP subsystem.');
			}
			
			return true;
		
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // end func connect
	
	/**
	 * Check connection.
	 *
	 * @access protected
	 * @return void
	 */
	protected function check_connection() {
		
		if(is_resource($this->conn_res)) {
			return true;
		}
		
		$this->connect();
		
	} // End func check_connection
	
	
	/**
	 * Disconnect from SFTP server.
	 *
	 * @access public
	 * @return bool
	 */
	public function close() {
		
		if(!is_resource($this->conn_res)) {
			return false;
		}
		
		try {
			
			ssh2_exec($this->conn_res, 'exit');
			unset($this->conn_res);
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // end func close
	
	/**
	 * Return files and directories list
	 * if $detailed == true, return a detailed list of files.
	 *
	 * @access public
	 * @param string $path = '.'
	 * @param bool $detailed = false
	 * @return mixed array | false
	 */
	public function ls($path = '.', $detailed = false) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			// Using the execution approach to get detailed information.
			if($detailed) {
				
				$stream = ssh2_exec($this->conn_res, 'ls -l');
				stream_set_blocking($stream, true);
				
				// The command may not finish properly if the stream is not read to end
				$output = stream_get_contents($stream);
				
				if(!$output) {
					throw new Exception('Unable to list files!');
				}
				
				$files = explode("\n", trim($output));
				
				// The first line of statistic data should be removed
				// It can looks like this: [0] => total 28K
				if(substr($files[0], 0, 5) == 'total') {
					array_shift($files);
				}
				
				return $files;
				
			} else {
				
				if($path == '/') {
					$path = '.';
				}
				
				// We always adding first slash, so we have to remove it if exists.
				$path = ltrim($path, '/');
				
				$sftp = $this->sftp_res;
				$dir = "ssh2.sftp://$sftp/$path";
				$files = array();
				
				$handle = opendir($dir);
				
				while (false !== ($file = readdir($handle))) {
					if (substr($file, 0, 1) != '.' and substr($file, 0, 2) != '..') {
						$files[] = $file;
					}
				}
				
				closedir($handle);
				
				return $files;
				
			}
				
		} catch (Exception $e) {
				
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
				
		}
				
	} // end func file_list
	
	/**
	 * Upload a file
	 *
	 * @access public
	 * @param string $src_file
	 * @param string $dst_file = NULL
	 * @return bool
	 */
	public function upload($src_file, $dst_file = NULL) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!is_readable($src_file) or !is_file($src_file)) {
				throw new Exception('File `'.$src_file.'` is not readable!');
			}
			
			if(is_null($dst_file)) {
				$dst_file = $src_file;
			}
			
			$sftp = $this->sftp_res;
			$stream = @fopen("ssh2.sftp://$sftp/$dst_file", 'w');
			
			if (!$stream) {
				throw new Exception('Could not open file `'.$dst_file.'` to write!');
			}
			
			$data_to_send = file_get_contents($src_file);
			
			if (fwrite($stream, $data_to_send) === false) {
				throw new Exception('Could not send data from file `'.$src_file.'`!');
			}
			
			fclose($stream);
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}

	} // End func upload
	
	/**
	 * Upload file content
	 *
	 * @access public
	 * @param string $content
	 * @param string $dst_file
	 * @return bool
	 */
	public function upload_content($content, $dst_file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			$sftp = $this->sftp_res;
			$stream = @fopen("ssh2.sftp://$sftp/$dst_file", 'w');
			
			if (!$stream) {
				throw new Exception('Could not open file `' . $dst_file . '` to write!');
			}
			
			if (@fwrite($stream, $content) === false) {
				throw new Exception('Could not write data!');
			}
			
			fclose($stream);
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
		
	} // End func upload_content
	
	/**
	 * Download file from SFTP server
	 *
	 * @access public
	 * @param string $src_file
	 * @param string $dst_file
	 * @return bool
	 */
	public function download($src_file, $dst_file) {
		
		// Connect if not connected
		$this->check_connection();
				
		if(is_null($dst_file)) {
			$dst_file = $src_file;
		}
		
		try {
			
			$sftp = $this->sftp_res;
			
			$stream = fopen("ssh2.sftp://$sftp/$src_file", 'r');
			
			$contents = fread($stream, filesize("ssh2.sftp://$sftp/$src_file"));
			file_put_contents($dst_file, $contents);
			
			fclose($stream);
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
		
	} // end func download_file
	
	/**
	 * Download file content to string
	 *
	 * @access public
	 * @param string $src_file
	 * @return mixed string | bool
	 */
	public function read($src_file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			$sftp = $this->sftp_res;
			$stream = fopen("ssh2.sftp://$sftp/$src_file", 'r');
			$content = fread($stream, filesize("ssh2.sftp://$sftp/$src_file"));
			
			fclose($stream);
			
			return $content;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // end func read
	
	/**
	 * Rename file
	 *
	 * @access public
	 * @param string $src_file
	 * @param string $dst_file
	 * @return bool
	 */
	public function rename($src_file, $dst_file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ssh2_sftp_rename($this->sftp_res, $src_file, $dst_file)) {
				throw new Exception('Unable to rename file `'.$src_file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // end func rename
	
	/**
	 * Delete file
	 *
	 * @access public
	 * @param string $file
	 * @return bool
	 */
	public function rm($file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ssh2_sftp_unlink($this->sftp_res, $file)) {
				throw new Exception('Unable to remove file `'.$file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // end func rm
	
	/**
	 * Return Current working directory path
	 *
	 * @access public
	 * @return string
	 */
	public function pwd() {
		
		// Connect if not connected
		$this->check_connection();
		
		return $this->exec_cmd('pwd');
				
	} // End func pwd
	
	/**
	 * Execute a shell command to string
	 *
	 * @access public
	 * @param string $command
	 * @return string
	 */
	public function exec_cmd($command) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!$stream = ssh2_exec($this->conn_res, $command)) {
				throw new Exception('Unable to execute command `'.$command.'`!');
			}
			
			stream_set_blocking($stream, true);
			
			$output = stream_get_contents($stream);
			
			fclose($stream);
			
			return $output;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // End func exec_cmd
	
	/**
	 * Change access right (chmod)
	 * Mode example: 0755
	 *
	 * @access public
	 * @param string $file
	 * @param int $mode
	 * @return bool
	 */
	public function chmod($file, $mode) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ssh2_sftp_chmod($this->sftp_res, $file, $mode)) {
				throw new Exception('Unable to change file mode `'.$file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
				
	} // End func chmod
	
	/**
	 * Get file size
	 *
	 * @access public
	 * @param string $file
	 * @return mixed
	 */
	public function size($file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			$stat_info = ssh2_sftp_stat($this->sftp_res, $file);
			
			if(!isset($stat_info['size'])) {
				throw new Exception('Unable to get file `'.$file.'` size!');
			}
			
			return $stat_info['size'];
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
		
	} // End func size
	
	/**
	 * Make a directory
	 *
	 * @access public
	 * @param string $dir
	 * @return mixed string | bool
	 */
	public function mkdir($dir) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ssh2_sftp_mkdir($this->sftp_res, $dir)) {
				throw new Exception('Unable to make directory `'.$dir.'`!');
			}
			
			return $dir;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
		
	} // End func mkdir
	
	/**
	 * Remove a directory
	 *
	 * @access public
	 * @param string $dir
	 * @return bool
	 */
	public function rmdir($dir) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ssh2_sftp_rmdir($this->sftp_res, $dir)) {
				throw new Exception('Unable to remove directory `'.$dir.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
			return false;
		}
		
	} // End func rmdir
	
} /* End of class sftp_lib */