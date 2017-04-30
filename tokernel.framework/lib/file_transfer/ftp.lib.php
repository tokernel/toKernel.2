<?php
/**
 * toKernel - Universal PHP Framework.
 * FTP class library.
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
 * @since      File available since Release 2.3.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * ftp_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class ftp_lib extends file_transfer_base_lib {
		
	/**
	 * System type identifier of
	 * the remote FTP server.
	 *
	 * @var string
	 * @access protected
	 */
	protected $sys_type;
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config) {
		
		// Set Default configuration for FTP
		$this->config = array(
			'driver' => 'ftp',
			'host' => 'localhost',
			'port' => '21',
			'use_ssl' => 0,
			'passive_mode' => 0,
			'timeout' => 90,
			'username' => '',
			'password' => '',
			'log_errors' => 1,
			'display_errors' => 1
		);
		
		parent::__construct($config);
	}
		
	/**
	 * Connect to FTP Server
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
		
		// ftp_ssl_connect() is only available if both the ftp module and the OpenSSL support is built statically into php.
		if(function_exists('ftp_ssl_connect') and $this->config['use_ssl']) {
			$conn_method = 'ftp_ssl_connect';
		} else {
			$conn_method = 'ftp_connect';
		}
		
		try {
			
			if(!$this->conn_res = $conn_method($this->config['host'], $this->config['port'])) {
				throw new Exception('Unable to connect - '.$this->config['host'].':'.$this->config['port']);
			}
			
			ftp_login($this->conn_res, $this->config['username'], $this->config['password']);
						
			if($this->config['passive_mode']) {
				ftp_pasv($this->conn_res, true);
			}
			
			$timeout = intval($this->config['timeout']);
			
			ftp_set_option($this->conn_res, FTP_TIMEOUT_SEC, $timeout);
			
			$this->sys_type = ftp_systype($this->conn_res);
			
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
	 * Disconnect from FTP server.
	 *
	 * @access public
	 * @return bool
	 */
	public function close() {
		
		if(!is_resource($this->conn_res)) {
			return false;
		}
		
		try {
			
			ftp_close($this->conn_res);
			
			unset($this->conn_res);
			return true;
			
		} catch (Exception $e) {
			
			$this->react_to_error($e->getMessage());
			return false;
			
		}
		
	} // end func close
	
	/**
	 * Return files list
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
			
			if($detailed) {
				$files = ftp_rawlist($this->conn_res, $path);
			} else {
				$files = ftp_nlist($this->conn_res, $path);
			}
						
			if(!$files) {
				throw new Exception('Unable to list files in ' . $path);
			}
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
						
	} // end func file_list
	
	/**
	 * Upload a file
	 *
	 * @access public
	 * @param string $src_file
	 * @param string $dst_file
	 * @param int $mode = FTP_ASCII
	 * @return bool
	 */
	public function upload($src_file, $dst_file, $mode = FTP_ASCII) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!is_readable($src_file) or !is_file($src_file)) {
				throw new Exception($src_file.'` is not a file or is not readable!');
			}
						
			if(!ftp_put($this->conn_res, $dst_file, $src_file, $mode)) {
				throw new Exception('Unable to upload file `'.$src_file.'`!');
			}
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
		
		return true;
		
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
			
			if(!$stream = fopen('data://text/plain,' . $content, 'r')) {
				throw new Exception('Unable to open file stream!');
			}
			
			if(!ftp_fput($this->conn_res, $dst_file, $stream, FTP_BINARY)) {
				throw new Exception('Unable to upload content!');
			}
			
			return fclose($stream);
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}

	} // End func upload_content
	
	/**
	 * Download file from FTP server
	 * By default, if the destination file already exists it will be overwritten.
	 *
	 * @access public
	 * @param string $src_file
	 * @param string $dst_file
	 * @param int $mode = FTP_ASCII | FTP_BINARY
	 * @return bool
	 */
	public function download($src_file, $dst_file, $mode = FTP_ASCII) {
		
		// Connect if not connected
		$this->check_connection();
		
		if(is_null($dst_file)) {
			$dst_file = $src_file;
		}
		
		try {
			
			ftp_get($this->conn_res, $dst_file, $src_file, $mode);
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
				
	} // end func download
	
	/**
	 * Download file content to string
	 *
	 * @access public
	 * @param string $src_file
	 * @return string
	 */
	public function read($src_file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			$handle = fopen('php://temp', 'r+');
			ftp_fget($this->conn_res, $handle, $src_file, FTP_BINARY, 0);
			rewind($handle);
			
			return stream_get_contents($handle);
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
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
			
			if(!ftp_rename($this->conn_res, $src_file, $dst_file)) {
				throw new Exception('Unable to rename file `'.$src_file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
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
			
			if(!ftp_delete($this->conn_res, $file)) {
				throw new Exception('Unable to remove file `'.$file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
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
		
		return ftp_pwd($this->conn_res);
		
	} // end func pwd
	
	/**
	 * Execute a shell command to string
	 *
	 * @access public
	 * @param string $command
	 * @return bool
	 */
	public function exec_cmd($command) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ftp_exec($this->conn_res, $command)) {
				throw new Exception('Unable to execute command `'.$command.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
				
	} // End func exec_cmd
	
	/**
	 * Change access right (chmod)
	 * Mode value example: 0644
	 *
	 * @access public
	 * @param string $file
	 * @param int $mode
	 * @return mixed
	 */
	public function chmod($file, $mode) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			if(!ftp_chmod($this->conn_res, $mode, $file)) {
				throw new Exception('Unable to change file mode `'.$file.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
				
	} // End func chmod
	
	/**
	 * Get file size
	 *
	 * @access public
	 * @param string $file
	 * @return mixed int | bool
	 */
	public function size($file) {
		
		// Connect if not connected
		$this->check_connection();
		
		try {
			
			$size = ftp_size($this->conn_res, $file);
				
			if($size > -1) {
				return $size;
			} else {
				throw new Exception('Unable to get file size `'.$file.'`!');
			}
									
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
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
			
			if(!$new_dir = ftp_mkdir($this->conn_res, $dir)) {
				throw new Exception('Unable to make directory `'.$dir.'`!');
			}
			
			return $new_dir;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
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
			
			if(!ftp_rmdir($this->conn_res, $dir)) {
				throw new Exception('Unable to remove directory `'.$dir.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
				
	} // End func rmdir
	
	/**
	 * Change current directory
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public function chdir($path)  {
		
		$this->check_connection();
		
		try {
			
			if(!ftp_chdir($this->conn_res, $path)) {
				throw new Exception('Unable to change directory to `'.$path.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
		
	} // end func chdir
	
	/**
	 * Allocates space for a file to be uploaded
	 *
	 * @access public
	 * @param mixed int | string $file_or_size
	 * @return bool
	 */
	public function mem_alloc($file_or_size) {
		
		$this->check_connection();
		
		try {
			
			if(!is_numeric($file_or_size)) {
				
				if(!file_exists($file_or_size)) {
					throw new Exception('File not exists `'.$file_or_size.'`!');
				}
				
				if(!$file_or_size = filesize($file_or_size)) {
					throw new Exception('Unable to get file size `'.$file_or_size.'`!');
				}
				
			}
			
			if(!ftp_alloc($this->conn_res, $file_or_size)) {
				throw new Exception('Unable to allocate file/size `'.$file_or_size.'`!');
			}
			
			return true;
			
		} catch (Exception $e) {
			$this->react_to_error($e->getMessage());
			return false;
		}
		
	} // end func mem_alloc
	
	/**
	 * Return system type identifier of the remote FTP server
	 *
	 * @access public
	 * @return string
	 */
	public function system_type() {
		
		$this->check_connection();
		
		return $this->sys_type;
		
	} // end func system_type

} /* End of class ftp_lib */
