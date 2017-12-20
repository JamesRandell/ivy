<?php
/**
 * JSON module for IVY3.0
 *
 * @author James Randell
 * @version 0.1
 * @package JSON
 */
class Ivy_Json
{
	private function __construct ()	{}
	
	public function merge ($newArray, $otherArray)
	{
		foreach($otherArray as $key => $value){
			if (!isset($newArray[$key])) {
				$newArray[$key] = array ();
			}
			
			if (!is_array($newArray[$key])) {
				$newArray[$key] = array ();
			}
			if (is_array($value)) {
				$newArray[$key] = self::merge($newArray[$key], $value);
			} else {
				$newArray[$key] = $value;
			}
		}
		return $newArray;
	}
}


?>