<?php
/* Holds the INI communication methods
*
* @description		API for interracting with INI files
* Use method: getInstance to instantiate
*/
class csv {

	private static $instance;
	
	
	public function __construct ()	{}
	
	public function connect () {}
	
	/*
	* Always returns the same instace of this object
	* @return	instance		instace of the parent
	*/
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new csv();
		}
		return self::$instance;
	}

	public function load ($fileName, $key = null)
	{
		$array = parse_ini_file($fileName, true);

		
		$result[0] = (($key) ? $array[$key] : $array);
		return $result;
	}
	
	public function disconnect () {}
}

?>