<?php
/**
 * Error module for IVY3.0.
 *
 * Used for generating error messages and saving them in the registry.  Also contains logic for dealing with error messages.
 * This class follows the Singleton pattarn and as such can't be instatiated directly
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Error
 */
class Ivy_Error {

	/**
	 * Contains reference to the current object.
	 *
	 * @access private
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Contains reference to the Registry object.
	 *
	 * @access private
	 * @var object
	 */
	private static $registry;
	
	/**
	 * Holds all errors generates by Ivy
	 *
	 * @access public
	 * @var object
	 */
	public $errors = array ();
	
	/**
	 * Internal Error counter.
	 *
	 * @access private
	 * @var int
	 */
	private $i = 0; /* counter */
	
	/**
	 * Default mode for assigning an error in a category.
	 *
	 * @access public
	 * @var string
	 */
	public $mode = 'default'; /* Modes are: default, validation, error, system */
	
	/**
	 * Private constructor to prevent multiple instances from being created.
	 *
	 * @access private
	 * @var array
	 */
	private function __construct () {}
	
	/**
	 * Show Stoppers
	 *
	 * @changed 05/03/2008	Removed 'validation' from the show stoppers array
	 * @access private
	 * @var array
	 */
	private $showStoppers = array('validation');

	/**
	 * Ensures only one instance of this class is accessible.
	 *
	 * @return object
	 */
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new Ivy_Error();
		}

		return self::$instance;
	}
	
	/**
	 * alias of insert
	 */
	public function add ($data = array ())
	{
		$this->insert($data);
	}	
	
	/**
	 * Creates an error message in the registry. 
	 *
	 * Checks for a mode, if none is passed then use the default
	 * Changes registry namespace to _error then creates the new error message.
	 *
	 * @param	mixed	$data	Contains an array of warning fields or a string that contains a description
	 * @param	string	$mode	Identifies the mode to use
	 */
	public function insert ($data)
	{

		$registry = Ivy_Registry::getInstance();
		if (!is_array($data)) {
			$registry->insertError(array('description'=>$data));
		} else {
			$registry->insertError($data);
		}
	}
	
	
	/**
	 * Gets current mode (default, error etc).
	 *
	 * Checks for a mode, if none is passed then use the default
	 * Changes registry namespace to _error then creates the new error message.
	 *
	 * @param	string	$mode	The type of error to retrieve
	 * @return	array
	 */
	public function getMode ($mode)
	{
		if (isset($this->errors[$mode])) {
			return $this->errors[$mode];
		} else {
			$this->add(array('title'=>'class: Error',
							'content'=>'Can not get records from a non-exsistent mode.'), 'error');
		}
	}
	
	/**
	 * Gets a specific error
	 */
	public function get () {}
	
	/**
	 * Gets all errors
	 *
	 * @return	array
	 */
	public function getAll ()
	{
		return $this->errors;
	}
	
	/**
	 * Looks for an entry, return true if one is found.
	 *
	 ** Checks for a mode, if none is passed then use the default
	 ** Changes registry namespace to _error then creates the new error message.
	 *
	 * @return bool
	**/
	public function check ()
	{
		$array = array ();
		foreach ($this->showStoppers as $mode) {
			$t = Ivy_Registry::getInstance()->selectError($mode);
			if (!empty($t)) {
				$array[] = $t;
			}
		}
		if (empty($array)) {
			return FALSE;
		} else {
			return $array;
		}
	}
	
	
	public function handler ($errno, $errstr, $errfile = NULL, $errline = NULL)
	{
		return;
		(string) $errfile = (is_null($errfile) ? $_SERVER['REQUEST_URI'] : $errfile);
		(int) $errline = (is_null($errline) ? '0' : $errline);
		(array) $array = array (); // main data store
		(array) $backtrace = array (); // for the debug backtrace fundtion
		
		$registry = Ivy_Registry::getInstance();
		
		
		$array['no'] = $errno;
		$array['str'] = $errstr;
		$array['filename'] = $errfile;
		$array['url'] = $_SERVER['REQUEST_URI'];
		$array['line'] = $errline;
		$array['site'] = SITE;
		$array['time'] = date("jS F Y @ g:ia", time());
		$array['collar'] = (isset($registry->session['collar']) ? $registry->session['collar'] : 'Unknown');
		/*foreach (debug_backtrace() as $key => $data) {
			if ($key != 'args') {
				$backtrace[$key] = $data;
			}
		
		}
		$array['backtrace'] = $backtrace;
		*/
		#echo $errstr.'-'.$errno.'<br>';
		
		(array) $errorMessages = array ();
		
		$errorMessages['Slow Script Execution'] = array('Script ran for');
		$errorMessages['Slow Function Execution'] = array();
		$errorMessages['Slow Query Execution'] = array();
		$errorMessages['Database Error'] = array('You have an error in your SQL syntax');
		$errorMessages['PHP Error'] = array('Use of undefined constant');
		$errorMessages['PHP Notice'] = array('Invalid argument supplied for foreach','The action');
		$errorMessages['Function Error'] = array();
		$errorMessages['Excess Memory Usage'] = array('Excess memory usage');
		$errorMessages['Load Average'] = array();
		$errorMessages['Ignore'] = array('Undefined index','Undefined variable','DOMDocument::loadHTML');

		switch ($errno) {
			case 1	:
				$array['type'] = 'PHP Error';
				break;			
			case 2	:
				$array['type'] = 'PHP Error';
				break;			
			case 8	:
				$array['type'] = 'PHP Notice';
				break;			
			case 256	:			
				$array['type'] = 'PHP Error';
				break;
			case 512	:			
				$array['type'] = 'PHP Error';
				break;
			case 1024	:			
				$array['type'] = 'PHP Notice';
				break;
			case 2048	:
				$array['type'] = 'PHP Notice';
				break;
			default		:
				$array['type'] = 'Unknown';
				break;
		}
		
		// loops through the error msg and tries to match them, then changes the TYPE
		// appropriatly
		foreach ($errorMessages as $key => $messages) {
			foreach ($messages as $id => $value) {
				$pos = strpos($errstr, $value);
				if ($pos !== FALSE) {
					if ($key == 'Ignore') {
						return;
					}
					$array['type'] = $key;
				}
			}
		}
		
		#$registry->insertError($array);
		$object = new Ivy_Database('ivy_error');
		
		$array['DATECREATED'] = time();
		$object->insert(array_change_key_case($array, CASE_UPPER));

		unset($array);
		unset($object);
		return true;

	}
	
	/**
	 *	__destruct.
	 */
	public function __destruct ()
	{
		#timerSelect()
		
		#$this->registry->delete('error');
	}
}

?>
