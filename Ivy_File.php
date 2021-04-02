<?php
/**
 * File module for IVY3.0
 *
 * @author James Randell
 * @version 0.1
 * @package File
 */
 
/**
 * Creates, updates and retrieves data to/from an XML file.
 * @package File
 */
class Ivy_File
{
	
public static $error = Object;

public function __construct ()	
{
	self::$error = Ivy_Error::getInstance();
	
}


public $data = array ();


public static function load ($path)
{
	if (file_exists($path)) {
		return file_get_contents($path);
		
	} else {
		self::$error->insert('File does not exist or cannot be read: ' . $path);

		print_pre(self::$error->select());

		return false;
	}
}

public static function readDir ($path)
{
	$array = array ();
	
	if (!is_dir($path)) {
		return array('error' => 'The directory does not exist');
	}
	
	if ($handle = opendir($path)) {
		while(($file = readdir($handle)) !== FALSE) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$array[] = $file;
		}
	}
	return $array;
}
/*
public static function select ($path = './')
{
	(array) $array = array ();
	
	if (!is_dir($path)) {
		if (is_file($path)) {
			return file_get_contents($path);
		}
		return FALSE;
	}
	
	if ($handle = opendir($path)) {
		while(($file = readdir($handle)) !== FALSE) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$array[] = $file;
		}
	} else {
		return FALSE;
	}
	
	return $array;
}
*/

public function select ($path = './', $recursive = false)
{
	(array) $array = array ();
	
	if (!is_dir($path) && $recursive === false) {
		if (is_file($path)) {
			return file_get_contents($path);
		}
		return FALSE;
	}
	
	if ($handle = opendir($path)) {
		while(($file = readdir($handle)) !== FALSE) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$tokens = explode('/', $path);
			
			
			
			$array[$tokens[sizeof($tokens)-2]] = $this->select(rtrim($path, '/') . '/' . $file, true);
			//$array[$tokens[sizeof($tokens)-2]][] = $file;
		}
	} else {
		return FALSE;
	}
	
	$this->data = $array;
	return $array;
}


public static function selectFile ($path = './')
{
	(array) $array = array ();
	
	if (!is_dir($path)) {
		if (is_file($path)) {
			return file_get_contents($path);
		}
		return FALSE;
	}
	
	if ($handle = opendir($path)) {
		while(($file = readdir($handle)) !== FALSE) {
			if (!is_file($path . '/' . $file) || $file == '.' || $file == '..') {
				continue;
			}
			$array[] = $file;
		}
	} else {
		return FALSE;
	}
	
	return $array;
}
	
/**
 * returns a list of folders only
 */
public static function selectFolder ($path = './')
{
	(array) $array = array ();
	
	if (!is_dir($path)) {
		return FALSE;
	}
	
	if ($handle = opendir($path)) {
		while(($folder = readdir($handle)) !== FALSE) {
			if (!is_dir($path . '/' . $folder) || $folder == '.' || 
				$folder == '..') {
				continue;
			}
			$array[] = $folder;
		}
	} else {
		return FALSE;
	}
	
	return $array;
}
	
public function delete ($file)
{
	return unlink($file);
}

public static function insert ($name, $id)
{
	(object) $registry = Ivy_Registry::getInstance();
	(string) $string = $registry->data->$id;
		if (file_put_contents($name, $string) === FALSE) {
		trigger_error('The file could not be written to: ' . $name );
	}
	
	return TRUE;
}

public static function copy ($source, $target)
{
	if (is_dir($source)) {
		mkdir($target);

		$d = dir($source);
            
		while (FALSE !== ($entry = $d->read())){
			if ($entry == '.' || $entry == '..'){
				continue;
			}

			$Entry = $source .'/'. $entry;            
			if (is_dir($Entry)) {
				Ivy_File::copy($Entry, $target . '/' . $entry);
				continue;
			}
			if (@copy($Entry, $target . '/' . $entry) === FALSE) {
				#echo "error copying '$Entry' to '$target/$entry'";echo '<br>';
				return FALSE;
			}
		}
		$d->close();
	} else {
		if (copy($source, $target) === FALSE) {
			return FALSE;
		}
	}
		
	return TRUE;
}
    
public static function uploadDetail ($array = array ())
{
	(array) $array;

	if (empty($array)) {
		if (isset($_FILES)) {
			$array = current($_FILES);
		} else {
			trigger_error('No $_FILES array found');
			return FALSE;
		}
	}
	if (!isset($array['name'])) {
		trigger_error('The name of the file was missing when getting file details');
		return FALSE;
	}
    	
	if (empty($array['name'])) {
		trigger_error('the name of the file was empty when getting file details');
		return FALSE;
	}
    	
	$nameArray = explode('.', $array['name']);
	$result['FILENAME'] = $array['name'];
	$result['FILESIZE'] = $array['size'];
	$result['FILETYPE'] = $array['type'];
	$result['FILEEXT'] = substr(strrchr($array['name'], '.'), 1);
	$result['FILETEMPNAME'] = $array['tmp_name'];
    	
	return $result;
    	
}
    
public function upload ($array = array ())
{
	(array) $array;

	if (!isset($array['PATH'])) { 
		$array['PATH'] = getcwd() . '/site/' . SITE . '/upload/';
	}
	if (!isset($array['tmp_name'])) { 
		$this->error[] = 'tmp_name was missing when trying to upload a file';
		return FALSE;
	}
	if (!isset($array['name'])) { 
		$this->error[] = 'name was missing when trying to upload a file';
		return FALSE;
	}
    	
	$uploadfile = $array['PATH'] . $array['name'];
	
	if (file_exists($uploadfile)) {
		$fileArray = explode('.', $uploadfile);
		$extension = $fileArray[ count($fileArray)-1 ];		
		unset($fileArray[ count($fileArray)-1 ]);
		$name = implode('.', $fileArray);
		
		$uploadfile = $name . time() . '.' . $extension;
	}
	if (move_uploaded_file($array['tmp_name'], $uploadfile)) {
		return $uploadfile;
	} else {
		$this->error[] = 'Upload failed';
		return false;
	}
}

public function readonly ($file) { $this->readable($file); }
public function readable ($file)
{
	
	if (!is_readable(file)) {
		self::$error->insert('File cannot be read: ' . $path);
	}

}

public function writeonly ($file) { $this->writeable($file); }
public function writeable ($file)
{


}

public function __destruct ()
{
	foreach ($this->error as $key => $value) {
		trigger_error($value);
	}
}
}


?>