<?php
/**
 *FTP module for IVY3.0.
 *
 * Built to make opening and closing connections, sending and retrieving files compatible with the rest of IVY. 
 * This is a simple API that can interface nicely with other data structures used by the framework.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Ftp
 */
class Ivy_Ftp 
{
	
	/**
	 * Username
	 *
	 * @access private
	 * @var string
	 */
	private $username = '';
	
	/**
	 * Password
	 *
	 * @access private
	 * @var string
	 */
	private $password = '';
	
	/**
	 * Server name
	 *
	 * @access private
	 * @var string
	 */
	private $server = '';
	
	/**
	 * Array of file names that have been saved to the temp directory
	 *
	 * @access private
	 * @var array
	 */
	private $files = array ();
	
	/**
	 * FTP connection object
	 *
	 * @access private
	 * @var obj
	 */
	private $connection;
	
	/**
	 * Name of the folder that stores FTP files
	 *
	 * @access private
	 * @var string
	 */
	private $tempDirectory = 'temp/';
	
	
	public function __construct ($array = array ())
	{
		$registry = Ivy_Registry::getInstance();
		$ftp = $registry->selectSystem('config');
		
		
		$this->server = (isset($array['server']) ? $array['server'] : $ftp['ftp']['server']);
		$this->username = (isset($array['username']) ? $array['username'] : $ftp['ftp']['username']);
		$this->password = (isset($array['password']) ? $array['password'] : $ftp['ftp']['password']);
		
		$this->connection = ftp_connect($this->server); 
		if (!ftp_login($this->connection, $this->username, $this->password)) {
			return "Could not log in with user '$this->username'.";
		}
	}
	
	
	
	private function disconnect () {}
	public function describe () {}
	
	/**
	 * Uses ftp_put to store files on a FTP server.  Can store directories or single files.
	 *
	 * If a single file is specified via directoy (path/to/file.png for example), function will cut the file name 
	 * and use that as the destination name. 
	 *
	 * @param	string	$source		The source location.  This can be a directory or a single file name.
	  * @param	string	$destination	Destination location on the FTP service.
	 */
	public function insert ($source, $destination = null)
	{
		if (!$destination) {
			$destination = $this->tempDirectory;
		}

		if (is_file($source)) {
			
			$fileNameArray = explode('/', $source);
			$file = $fileNameArray[ count($fileNameArray) -1 ];

			$result = ftp_put($this->connection, $file, $source, FTP_BINARY);
			return $result;
		} else if (is_dir($source)) {
			
			if ($handle = opendir($source)) {
				while(($file = readdir($handle)) !== FALSE) {
					if ($file == '.' || $file == '..') {
						continue;
					}
					$result = ftp_put($this->connection, $file, $source . $file, FTP_BINARY);
				}
				return $result;
			}
			
		}
		
		
	}
	
	
	public function select ($file = null)
	{
		if (!$file) {
			$list = ftp_nlist($this->connection, '');
			foreach ($list as $file) {
				$result = ftp_get($this->connection, $this->tempDirectory . $file, $file, FTP_BINARY);
				$this->files[$file] = $result;	
			}
		} else {
			$result = ftp_get($this->connection, $this->tempDirectory . $file, $file, FTP_BINARY);
			$this->files[$file] = $result;
		}
	}
	
	/**
	 * Delete a file
	 *
	 * @param	string	$file		What to delete?
	 */
	public function delete ($file)
	{
		ftp_delete($this->connection, $file);
	}
	

	/**
	 * Returns the files array
	 */
	public function files ()
	{
		return $this->files;
	}
	
	
	public function test ()
	{
		// login with username and password
		$login_result = ftp_login($this->connection, $this->username, $this->password); 

		// check connection
		if ((!$this->connection) || (!$login_result)) { 
	        echo "FTP connection has failed!<br />";
	        echo "Attempted to connect to $ftp_server for user $ftp_user_name<br />"; 
	        exit; 
	    } else {
	        echo "Connected to $server, for user $username<br />";
	    }
			
		$source = 'system/config.ini';
		$destination = '43243';
		
		// upload the file
		$upload = ftp_put($this->connection, $destination, $source, FTP_BINARY); 

		// check upload status
		if (!$upload) { 
		    echo "FTP upload has failed!<br />";
		} else {
	        echo "Uploaded $source to $server as $destination<br />";
	    }

		// close the FTP stream 
		ftp_close($this->connection); 

}

}
?>