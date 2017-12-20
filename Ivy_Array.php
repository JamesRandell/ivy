<?php
/**
 * SVN FILE: $Id: Ivy_Array.php 18 2008-10-01 11:01:03Z shadowpaktu $
 *
 * Project Name : Project Description
 *
 * @package IVY_Array
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 18 $
 * @lastrevision $Date: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Array.php $
 */

 
class Ivy_Array {


private function __construct ()	{}
	
public static function merge ($newArray, $otherArray = array ()) {

	if (!empty($otherArray)) {
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
	}
		
	return $newArray;
}
	
/**
 * Takes a needle and haystack (just like in_array()) and does a wildcard 
 * search on it's values.
 *
 * @param	string		$string		Needle to find
 * @param	array		$haystack	Haystack to look through
 * @result	array					Returns the elements that the $string was found in
 */
public static function find ($string, $haystack = array ()) {
	$array = array ();
		
	foreach ($haystack as $key => $value) {
		if (strpos(strtolower($value), strtolower($string)) !== false) {
			$array[] = $value;
		}
	}
	return $array;
}


}


?>