<?php
/* Holds the MySQl database communication methods
* @param	$instance	object	holds the current and single instance of this object
* @param	$connection	resource	contains the open connection (if any) to a MySQL database
* @param	$queryCount	int		holds the current result set to be returned to the controlling object
* @method	getInstance			returns the current object
* @method	connect				attempts to connect to a MySQL database
* @method	query				attempts to perform a query on the connected database
* @method	disconnect			closes the open connection to the MsSQL database
*
* @description		This class follows the Singleton pattarn and as such can't be instatiated directly
* Use method: getInstance to instantiate
*/
class mssql {

	private static $instance;
	private $connection;
	private $queryCount = 0;
	
	private $server = '';
	private $database = '';
	private $username = '';
	private $password = '';
		

	/**
	 * allows the processing of parameterised queries
	 * @return 	array 	empty array
	 */
	public $parameter = array ();
	

	private function __construct ()	{}
	
	/*
	* Always returns the same instace of this object
	* @return	instance		instace of the parent
	*/
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new mssql();
		}
		

		return self::$instance;
	}

	/*
	* Attempts to open a connection to a MsSQL database
	* @return	resource		A MsSQL connection resource
	*/
public function	connect ($databaseSpec = array ()) {
	if (!$this->connection) {
		ini_set('mssql.min_error_severity', 1);
		ini_set('mssql.min_message_severity', 1);
		
		if (isset($databaseSpec['server'])) {		
			$this->server = $databaseSpec['server'];
			$this->database = $databaseSpec['database'];
			$this->username = $databaseSpec['username'];
			$this->password = $databaseSpec['password'];
		} else {
			$registry = Ivy_Registry::getInstance();
			$config = $registry->selectSystem('config');

			$this->server = $config['db']['server'];
			$this->database = $config['db']['database'];
			$this->username = $config['db']['username'];
			$this->password = $config['db']['password'];
		}
		
		if (empty($this->username) && empty($this->password)) {
			$info = array(	"Database"	=> 	$this->database,
							//"ReturnDatesAsStrings"	=> true
						);
		} else {
			$info = array(	"Database"	=> 	$this->database,
							//"ReturnDatesAsStrings"	=> true,
							"UID"		=>	$this->username,
							"PWD"		=>	$this->password
						);
		}
		
		$this->connection = sqlsrv_connect($this->server, $info);


		if (!$this->connection = sqlsrv_connect($this->server, $info)){
			trigger_error('Could not connect to database using ' . 
				"Server: $this->server, " .
				"Username: $this->username, " .
				"Password: $this->password", E_USER_ERROR);
			
			return false;
		}
		
		
		return $this->connection;
	}

	return $this->connection;
}

/** 
 * Runs the query passed to it (pre-valdiated and checked)
 * @param	$query	string	an SQL query to be run.
 * @return			int		the key to the dataStore, used for retrieving the
 * data
*/
public function query ($query) {
	#echo $query .'<br><br><br><bR><br>';

	if (!$this->connection) {
		return false;
	}
	
	/**
	 * strip out some random charectors that crept in.
	 * this is an old piece of code, should it be removed?
	 *
	 * @datemodified	04/04/2017
	 * @author 			James Randell <james.randell@curtisfitchglobal.com>
	 */
	$query = preg_replace('/\|\|/', "+", $query);
	$query = str_replace ("&#039;", "''", $query);
	$query = urldecode(str_replace("%26%2339", "''", urlencode($query)));

	
	(array) $result = array ();
	
	if (!empty($this->parameter)) {

		foreach ($this->parameter as $key => $value) {
			$where_array[] = $value['value'];
		}

		
	}

	if (!$resultSet = sqlsrv_query($this->connection, $query, $where_array)) {
		trigger_error('MSSQL error: ' . sqlsrv_errors() . ' - ' . $query);
		echo '<pre>';
		var_dump( sqlsrv_errors() );
		echo '</pre>';
		echo '<br />' . $query.'<br><br>';
		print_pre($where_array);
		die();
	}

	/**
	 * using the sqlsrv_has_rows lets us know if we have a result set or not.
	 * if the result is TRUE, then it's a SELECTing data.
	 * if it returns FALSE, then we know it's atelast an INSERT, UPDATE or DELETE.
	 */
	if (sqlsrv_has_rows($resultSet) === true) {
		// run the logic for SELECTs

		while ($line = sqlsrv_fetch_array($resultSet)) {
			foreach ($line as $key => &$value) {

				/**
				 * THe PHP SQL driver does something clever by interpreting the datetime stamp as a PHP DateTime Object
				 * So instead of treating it as a string, understand the object and return the correct result
				 *
				 * I've now overriden this in the conenction with 
				 *			"ReturnDatesAsStrings"	=> true
				 */
				
				
				if (is_object($value)) {

					/**
					 * We use the get_object_vars method because (now this is funny), if we try to get the date object direct ($value->date)
					 * it's blank. Its almost behaving like a .bat script where you need DELAYEDEXPANSION on. Running the below populate the
					 * date object, and we just reference the string via an array instead of by object
					 */
					$temp = (array) get_object_vars($value);
					$value = (string) $temp['date'];

				/*
				} else if (is_int) 		{	$value = (int) $value;
				} else if (is_float) 	{
				} else if (is_scaler) 	{
				} else if (is_double) 	{
				} else if (is_long) 	{
				} else if (is_bool) 	{
				} else if (is_numeric) 	{	$value = (int) $value;
				*/
					
				} else if (strpos($value, "'") !== false) {
					
					$value = str_replace("'", "&#039;", $value);
					
				}
			}
			
			$row[] = $line;
			
			
		}
		if (isset($row)) {
			$result = $row;
		}

	} else {
		// run the logic for INSERTs, UPDATEs, DELETEs and other statements

		$sql = sqlsrv_query($this->connection, 'SELECT SCOPE_IDENTITY() AS ID');
		$result = sqlsrv_fetch_array($sql);
		$result = $result['ID'];

	}
	
	$this->queryCount++;
	return $result;
}

/**
 * Pagination for MsSQL
 * 
 * Accepts an array of parameters the specify first and last record, primary 
 * key and the query. The method uses a suitable way of paginating results.
 */
public function pagination ($details) {	
	(int) $range = $details['last'] - $details['first'];
	(int) $first = $details['first'];
	(int) $last = $details['last'];
	(string) $order = $details['order'];
	(string) $pk = $details['pk'];
	(int) $query =$details['query'];


	/**
	 * Grab all the records so we can do a GROUP BY expression on on the sub
	 * query
	 */
	$query = preg_replace('/SELECT/',
		"SELECT TOP 100 PERCENT ", $query, 1);
	
	/**
	 * Add a MsSQL rownum function so we can accuratly get the record count
	 */
	$temp = explode('FROM', $query);


	$o = str_replace('"', '', $order);
	$temp[ count($temp) -2  ] = $temp[ count($temp) -2  ] . ", ROW_NUMBER() OVER ($o) AS rnum ";
	$query = implode('FROM', $temp);

	/**
	 * Build the query with the first and last record values and return it
	 */
	$query = "SELECT *
	    FROM ($query) as eee
	    WHERE rnum <= $last
	    AND   rnum >= $first" . $order;

	#echo $first.'-'.$last;
	return $query;
}

public function translateFieldTypes ($type) {
	switch ($type) {
		case 'int'	:
			return 'int';
			break;
		case 'unix'	:
			return 'int';
			break;
		case 'var'	:
			return 'varchar';
			break;
		case 'clob'	:
			return 'TEXT';
			break;
		default		:
			return $type;
	}
}
	
public function createTable ($schema) {
	$string = 'CREATE TABLE "' . $schema['tableSpec']['name'] . '" (';
	foreach ($schema['fieldSpec'] as $field => $data) {			
	
		if ($this->translateFieldTypes($data['back']['type']) == 'int') {
			$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
		} else if ($this->translateFieldTypes($data['back']['type']) == 'text') {
			$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
		} else if ($this->translateFieldTypes($data['back']['type']) == 'datetime') {
			$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
		} else if ($this->translateFieldTypes($data['back']['type']) == 'date') {
			$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
		} else if ($this->translateFieldTypes($data['back']['type']) == 'time') {
			$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
		} else {
			if (isset($data['back']['size'])) {
				$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']) . '(' . 						$data['back']['size'] . ')';
			} else {
				$string .= '  "' . $field . '" ' . $this->translateFieldTypes($data['back']['type']);
			}
		}
		
		if (isset($data['back']['auto']) && $schema['tableSpec']['pk'][0] == $field) {
			$string .=  ' IDENTITY(1,1)';
		} else {
			if (isset($data['back']['default'])) {
				
				switch ($data['back']['default']) {
					case 'datetime'	:	$string .= ' DEFAULT GETDATE()';
										break;
					case 'date'	:	$string .= ' DEFAULT GETDATE()';
										break;
					case 'time'	:	$string .= ' DEFAULT GETDATE()';
										break;
					default 	:	$string .= " DEFAULT '".$data['back']['default']."'";
				}

			} else {
				if (isset($data['back']['required']) && $data['back']['required'] == 'y') {
					$string .= ' NOT NULL';
				} else {
					$string .= ' NULL';
				}
			}
		}

		
		
		$string .= ',';			
	}
	
	/**
	 * Handles the creation of indexes from the tableSpec
	 *
	 * Right now it only expects the PK and INDEX, but later i would expand the functionality to include FKs
	 *
	 * @author 	James Randell <james.randell@curtisfitchglobal.com>
	 * @datemodified	04/04/2017
	 */
	foreach ($schema['tableSpec']['pk'] as $key => $value) {
		$string .= 'CONSTRAINT "PK_' . $schema['tableSpec']['name'] . '" PRIMARY KEY CLUSTERED ';
		$string .= '("' . $value . '" ASC)';
	}
	
	$string = rtrim($string, ',');
	$string .= ')';

	/**
	 * loops through all the indexes for this DDL. Once insdie, it then loops through keys such as FIELD and INCLUDED/COVERING
	 */
	foreach ($schema['tableSpec']['index'] as $index_name => $index_data) {
		$string .= ' CREATE NONCLUSTERED INDEX [IX_' . $schema['tableSpec']['name'] . '_' . $index_name . '] ON "dbo"."' . $schema['tableSpec']['name'] . '"';

		$string .= '(';
		foreach ($index_data['field'] as $key => $value) {
			$string .= '"' . $value . '" ASC,';
		}

		$string = rtrim($string, ',');
		$string .= ')';
	}


	return $string;
}
		
/* 
* Closes the open connection to to theMsSQLL database
*/
public function	disconnect () {
	if (is_bool($this->connection)) {

	}
}
	
}

?>