<?php
/* Holds the MySQl database communication methods
* @param	$instance	object	holds the current and single instance of this object
* @param	$connection	resource	contains the open connection (if any) to a MySQL database
* @param	$queryCount	int		holds the current result set to be returned to the controlling object
* @method	getInstance			returns the current object
* @method	connect				attempts to connect to a MySQL database
* @method	query				attempts to perform a query on the connected database
* @method	disconnect			closes the open connection to the MySQL database
*
* @description		This class follows the Singleton pattarn and as such can't be instatiated directly
* Use method: getInstance to instantiate
*/
class oracle {

	private static $instance;
	private $connection;
	private $queryCount = 0;
	
	
	private $server = '';
	private $username = '';
	private $password = '';
	
	private function __construct ()	{}
	
	/*
	* Always returns the same instace of this object
	* @return	instance		instace of the parent
	*/
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new oracle();
		}

		
		return self::$instance;
	}

	/*
	* Attempts to open a connection to a MySQL database
	* @return	resource		A MySQL connection resource
	*/
	public function	connect ($databaseSpec = array ())
	{
		
		$this->error = Ivy_Error::getInstance();
		$registry = Ivy_Registry::getInstance();
		
		$config = $registry->selectSystem('config');

		$this->server = $config['db']['server'];
		$this->username = $config['db']['username'];
		$this->password = $config['db']['password'];

		if (isset($databaseSpec['server'])) {		
			$this->server = (($databaseSpec['server']) ? $databaseSpec['server'] : $this->server);
			$this->username = (($databaseSpec['username']) ? $databaseSpec['username'] : $this->username);
			$this->password = (($databaseSpec['password']) ? $databaseSpec['password'] : $this->password);
		}

		if (!$this->connection = oci_connect($this->username, $this->password, $this->server)) {
			$this->error->add(array('type'		=>	'OCI connect',
									'msg'		=>	'ERROR!!!!!'));
			trigger_error('Could not connect to database using ' . "$this->server $this->username $this->password", E_USER_ERROR);
		}

		
		return $this->connection;
	}



public function pagination ($parameters)
{	
	return false;
}


	/* 
	* Runs the query passed to it (pre-valdiated and checked)
	* @param	$query	string	an SQL query to be run.
	* @return			int		the key to the dataStore, used for retrieving the data
	*/
	public function query ($query)
	{
	#echo $query.'<br><br><br>';
		
		(object) $registry = Ivy_Registry::getInstance();
		(array) $config = $registry->selectSystem('config');
		
		if (isset($config['db']['debug']) && $config['db']['debug'] == 1) {
			echo $query.'<br><br><br>';
			
			$registry = Ivy_Registry::getInstance();
			
			$registry->insertSystem('debug', $query);
			
		}
		
		(array) $row = array ();
		$result = oci_parse($this->connection, $query);
		
		if(oci_execute($result)){
			if ($rowCount = @oci_fetch_all($result, $row, "0", "-1", OCI_ASSOC+OCI_FETCHSTATEMENT_BY_ROW)) {
			
			}
		}

		$this->queryCount++;

		if (isset($row)) {
			return $row;
		} else {
			return TRUE;
		}
	}
	
	/* 
	* Closes the open connection to to the MySQL database
	*/
	public function	disconnect ()
	{	
		if (is_bool($this->connection)) {

		}
	}
	
	/**
	 * Translates the field type in the fieldSpec to that which can be used by the database engine
	 *
	 * @param	string	$type	The field type
	 * @return	string
	 */
	public function translateFieldTypes ($type)
	{
	
		switch ($type) {
			case 'int'	:
				return 'number';
				break;
			case 'unix'	:
				return 'number';
				break;
			case 'var'	:
				return 'varchar2';
				break;
			default		:
				return $type;
		}
	
	}
	
	public function createTable ($schema)
	{
		$string = 'CREATE TABLE ' . $schema['tableSpec']['name'] . ' (<br />';
		foreach ($schema['fieldSpec'] as $field => $data) {			
			if (isset($data['back']['size'])) {
				$string .= '  ' . $field . ' ' . $this->translateFieldTypes($data['back']['type']) . '(' . $data['back']['size'] . ')';
			 } else {
			 	$string .= '  ' . $field . ' ' . $this->translateFieldTypes($data['back']['type']);
			}
			
			if (isset($data['back']['required']) && $data['back']['required'] == 'y') {
				$string .= ' NOT NULL';
			} else {
				$string .= ' NULL';
			}
			$string .= ',<br />';			
		}
		
		$string .= 'CONSTRAINT "' . $schema['tableSpec']['name'] . '_PK" PRIMARY KEY ("' . $schema['tableSpec']['pk'][0] . '") ENABLE';
		
		$string = rtrim($string, ',<br />') . '<br />);';
		
		$string .= '<br />';
		#$string .= 'ALTER TABLE ' . $schema['tableSpec']['name'] . '<br />';
		
		#$string .= 'ADD (CONSTRAINT ' . $schema['tableSpec']['name'] . '_pk PRIMARY KEY(' . $schema['tableSpec']['pk'][0] . '));<br />';

		if (!isset($schema['tableSpec']['auto'])) {
			$string .= 'CREATE SEQUENCE ' . $schema['tableSpec']['name'] . '_seq START WITH 1 INCREMENT BY 1;<br />';
	
			$string .= 'CREATE OR REPLACE TRIGGER ' . $schema['tableSpec']['name'] . '__' . $schema['tableSpec']['pk'][0] . '__trig<br />';
			
			$string .= 'BEFORE INSERT ON ' . $schema['tableSpec']['name'] . ' FOR EACH ROW<br />';
			$string .= 'begin<br />';
	  
			$string .= '  select ' . $schema['tableSpec']['name'] . '_seq.nextval into :new.' . $schema['tableSpec']['pk'][0] . ' from dual;<br />';
			$string .= 'end;';
		}
		$string .= '<br />';



		return $string;
	}
	
	
}

?>