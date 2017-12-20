<?php
/* Holds the INI communication methods
*
* @description		API for interracting with INI files
* Use method: getInstance to instantiate
*/
class ini {

	private static $instance;
	
	
	public function __construct ()	{}
	
	public function connect () {}
	
	public function query () {}
	
	public function disconnect () {}
	
	
	/*
	* Always returns the same instace of this object
	* @return	instance		instace of the parent
	*/
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new ini();
		}
		return self::$instance;
	}

	public function load ($fileName, $key = NULL)
	{
		$array = parse_ini_file($fileName, true);

		
		$result[0] = (($key) ? $array[$key] : $array);
		return $result;
	}
	
	public function createTable () {}
}

?>