<?php
/**
 * Deals with storing system information.
 * 
 * Registry class. Stores the following information: Data, System, Error and
 * Session. This class follows the Singleton pattarn and as such can't be
 * instatiated directly.
 * 
 * @category	Ivy
 * @package		core
 * @author		James Randell <james.randell@ivyframework.co.uk>
 * @copyright	2006 - 2009 Ivy
 * @license		https://ivy.svn.sourceforge.net/svnroot/ivy/LICENSE.txt
 * @version		$Id:$
 * @tutorial	http://ivyframework.com/tutorial/Ivy_Registry.php
 */

class Ivy_Registry
{

/**
 * Contains reference to the current object.
 *
 * @access public
 * @var object
 */
public static $instance;
	
/**
 * Stores all data passed to the registry.
 *
 * @access public
 * @var array
 */
public $system = array ();
public $data = array ();
public $session = array ();
public $error = array ();

/**
 * Private constructor to prevent multiple instances from being created.
 *
 * @access private
 * @var array
 */
private function __construct () {}

/**
 * Returns a single a single object of the class only
 *
 * @return object
 */
public static function getInstance ()
{
	if (empty(self::$instance)) {
		session_start();

		self::$instance = new Ivy_Registry;

		if (isset($_SESSION)) {
			foreach ($_SESSION[SITE] as $key => $data) {
				self::$instance->$key = unserialize($data);
			}
		}		
	}
	return self::$instance;
}

/**
 * Creates a new Data entry.
 * If no $name is supplied then auto increment the regsistry, otherwise
 * increment and return the count.
 *
 * @param	mixed	$name	Name of the data to set, could also be the data
 * 							itself	 if it has no name.
 * @param	mixed	$value	Either NULL if no name is set.
 * 
 * @return	int
 */
public function insertData ($name, $value = null)
{
	(int) $countData 	= count($this->data);
	(int) $countError 	= count($this->error);

	if (!$value) {
		$this->data[$countData] = $name;
	} else {
		$this->data[$name] = Ivy_Array::merge($this->data[$name], $value);	
		$countData = $name;
	}
	
	return $countData;
}
	
/**
 * Creates a new System entry.
 * 
 * System array will always consist of a $name => $value pair.
 *
 * @param	string		$name		Name of the array to set.
 * @param	array		$value		The array being passed
 * @return	int
 */
public function insertSystem ($name, $value, $update = true)
{
	if (isset($this->system[$name])) {
		if (is_array($value)) {
			$this->system[$name] = Ivy_Array::merge($this->system[$name], $value);
		} else {
			$this->system[$name][] = $value;
		}
	} else {
		if (is_array($value)) {
			$this->system[$name] = $value;
		} else {
			$this->system[$name][] = $value;
		}
	}

	return count($this->system);
}
	
/**
 * Creates a new Error entry.
 *
 * System array will always consist of a $name => $value pair.
 *
 * @param	string		$name		Name of the array to set.
 * @param	array		$value		The array being passed
 * @return	int
 */
public function error ($value)
{
	return $this->insertError($value);
}

public function insertError ($value)
{		
	if (!isset($this->error['app'])) {
		$this->error['app'] = array ();
	}
	
	if (!isset($this->error['validation'])) {
		$this->error['validation'] = array ();
	}

	if (isset($value['type'])) {
		switch ($value['type']) {
			case 'validation'	:
				$this->error['validation'][$value['field']]
				[ count($this->error['validation'][$value['field']]) ] = $value;
				break;
			default				:
				$this->error['app'][ count($this->error['app']) ] = $value;
				break;
		}
	} else {
		$count = count($this->error['app']);
		$this->error['app'][$count] = $value;
		//$this->error['app'][$count] = $value['type'] = 'notice';
	}
}
	
/**
 * Creates a new Session entry.
 *
 * System array will always consist of a $name => $value pair.
 *
 * @param	string		$name		Name of the array to set.
 * @param	array		$value		The array being passed
 * @return	int
 */
public function insertSession ($value = array ())
{
	$this->session = Ivy_Array::merge($this->session, $value);
}
	
/** 
 * A simple registry search method
 * 
 * First checks to see if the a variable with the
 * same name exists and generates a non blocking error, It then adds to or
 * replaces the variable in the data array.
 *
 * @todo	Do the registry need a search feature? If so then why is it held in
 * 			this specifc class
 * @param	$name		name of the variable to be set
 */
public function find ($name = NULL)
{
	if ($name) {
		if (isset($this->data[$name])) {
			return true;
		} else {
			return false;
		}
	} else {
		if (isset($this->data)) {
			return true;
		} else {
			return false;
		}
	}
}
	
/**
 * Selects a variable from the Data array.
 *
 * @param	int		$key	ID of the array to retrieve
 * @return	array
 */
public function selectData ($key)
{
	$value = (isset($this->data[$key])) ? $this->data[$key] : FALSE;
	return $value;
}

/**
 * Selects a variable from the System array.
 *
 * @param	string		$key	Name of the array to retrieve.
 * @return	array
 */
public function selectSystem ($key = null)
{
	if (!$key) {
		return $this->system;
	}
		
	$value = (isset($this->system[$key])) ? $this->system[$key] : false;
	return $value;
}

/**
 * Selects a variable from the Error array.
 *
 * @param	string	$key	Name of the array to retrieve.
 * @return	mixed
 */
public function selectError ($key = null)
{	
	if ($key) {
		return (isset($this->error[$key]) ? $this->error[$key] : false);
	} else {
		return $this->error;
	}
}
	
/**
 * Selects a variable from the Session array.
 *
 * @param	int		$key	Name of the array to retrieve.
 * @return	array
 */
public function selectSession ($key = null)
{
	if ($key) {
		if (isset($this->session->$key)) {
			return $this->session->$key;
		} else {
			return $this->session[ $key ];
		}
	}

	return $this->session;
}
	
public function saveSession ()
{
	(string) $session = serialize($this->session);
	
	$_SESSION[SITE]['session'] = $session;
}
	
public function deleteSession ($key) {}
	
/**
 * Retrieves all data form the Registry
 *
 * @return	array
 */
public function selectAll ()
{
	return $this->data;
}
	
public function selectNode ($node)
{
	return $this->data[$node];
}
	
/**
 * Removes an entry from an array in the Registry
 *
 * @param	string	$key	Name of the array to remove.
 */
public function delete ($key)
{
	unset($this->$key);
}

public function reset ()
{
	$_SESSION = array ();
	$this->session = array ();
	$this->data = array ();
	$this->system = array ();
	$this->error = array ();
}

/**
 * Saves the session part of the registry as a session variable
 */
public function  __destruct ()
{
	$_SESSION[SITE]['session'] = serialize($this->session);
}


}
?>