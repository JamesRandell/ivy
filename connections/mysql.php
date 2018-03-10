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
class MySQL {

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
	public static function getInstance () {
		if (empty(self::$instance)) {
			self::$instance = new MySQL();
		}

		return self::$instance;
	}

	/*
	* Attempts to open a connection to a MySQL database
	* @return	resource		A MySQL connection resource
	*/
	public function	connect ($databaseSpec = array ()) {
		$this->error = Ivy_Error::getInstance();
		
		if (!$this->connection) {
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
			
			if (!$this->connection = new mysqli($this->server, $this->username, $this->password)) {
				trigger_error('Could not connect to database using ' . 
								"$this->server $this->username $this->password $this->database" . 
								mysqli_error(), E_USER_ERROR);
				return FALSE;
			}
			
			if (!mysqli_select_db($this->connection, $this->database)) {
				die('Could not connect to database: ' . $this->database);
				return FALSE;
			}
			
			$this->connection->query("SET sql_mode = 'ANSI_QUOTES,PIPES_AS_CONCAT'");
			//$this->connection->query("SET sql_mode='PIPES_AS_CONCAT'");
			return $this->connection;
			
		}
		#mysql_query("SET GLOBAL TRANSACTION ISOLATION LEVEL SERIALIZABLE");
		#mysql_query("SET GLOBAL sql_mode = 'ANSI'");
		return $this->connection;

	}
	
	/* 
	* Runs the query passed to it (pre-valdiated and checked)
	* @param	$query	string	an SQL query to be run.
	* @return			int		the key to the dataStore, used for retrieving the data
	*/
	public function query ($sql) {
		//echo $query.'<br><br><br>';

		(array) $result = array ();

		// mysql doesn't like the + sign for concat, so change it for double pipe
		$sql = str_replace('+', '||', $sql);
		
		//echo $sql.'              <br><br><br>';
		
		if (!empty($this->parameter)) {
			$params = array ();
			foreach($this->parameter as $param => $value) {
				array_push($params, $value['value']);
			}
			
			$types = '';
			foreach($params as $param) {        
				if(is_numeric($param)) {
					$types .= 'i';              //integer
				} elseif (is_float($param)) {
					$types .= 'd';              //double
				} elseif (is_string($param)) {
					$types .= 's';              //string
				} else {
					$types .= 'b';              //blob and unknown
				}
			}
			
			$bind_names[] = $types;
			
			for ($i=0; $i<count($params);$i++) {
				$bind_name = 'bind'.$i;  		//generate a name for variable bind1, bind2, bind3...     
				$$bind_name = $params[$i];		//create a variable with this name and put value in it
				$bind_names[] = & $$bind_name; 	//put a link to this variable in array  
			}
            
            
			//array_unshift($params, $types);
		}

#echo $sql .'<br>';


		//$query = $this->connection->stmt_init();


		if ($stmt = $this->connection->prepare($sql)) {
		
		
			// Bind Params
			if ($bind_names) {
				call_user_func_array(array($stmt,'bind_param'),$bind_names);
			}


			$stmt->execute(); 
			$meta = $stmt->result_metadata();

			if ($meta !== false) {
				
				/**
				 * only run if we have results back - don't bother with anything except SELECT
				 */
				$fields = $result = array();

				// This is the tricky bit dynamically creating an array of variables to use
				// to bind the results
				while ($field = $meta->fetch_field()) {
					$var = $field->name;
					$$var = null; 
					$fields[$var] = &$$var; 
				}

				$fieldCount = count($fields);               
				call_user_func_array(array($stmt,'bind_result'),$fields);

				// Fetch Results
				$i = 0;
				while ($stmt->fetch()) {
					$result[$i] = array();
					foreach($fields as $k => $v)
						$result[$i][$k] = $v;
					$i++;
				}
			} else {
				//$result = mysql_insert_id($stmt);
				$result = $this->connection->insert_id;
				//$sql = sqlsrv_query($this->connection, 'SELECT SCOPE_IDENTITY() AS ID');
				//$result = sqlsrv_fetch_array($sql);
				//$result = $result['ID'];
			}

			$stmt->close();
			//print_pre($result);
		}

		/*
		if ($sql->execute() === true) {
			$resultSet = $sql->bind_result();
			print_pre($sql);
		} else {
			trigger_error('There was an error running the query [' . $query . ']', E_USER_ERROR);
		}
		*/

		/*
		if (is_bool($resultSet)) {
			if ($resultSet == TRUE) {
				//$result = $this->queryCount; // The query against the database has succeeded
				return $result = $this->connection->insert_id;
				var_dump($result);
			} else {				
				return FALSE;
			}
		} else {
			while ($line = mysqli_fetch_array($resultSet)) {
				$row[] = $line;
			}
			
			mysqli_free_result($resultSet);
	
			if (isset($row)) {
				$result = $row;
				#$registry = Registry::getInstance();
				#$registry->namespace = 'query';
				#$registry->dataCount = $this->queryCount;
				#$dataDataArray[$result]['data'] = $row;
				#$registry->insert($dataDataArray);
			}
		}
		*/
		$this->queryCount++;

		return $result;
	}

public function pagination ($parameters) {	
	return false;
}


	/* 
	* Closes the open connection to to the MySQL database
	*/
	public function	disconnect () {	
		if (is_bool($this->connection)) {
			mysqli_close($this->connection);
		}
	}
	
	/**
	 * Translates the field type in the fieldSpec to that which can be used by the database engine
	 *
	 * @param	string	$type	The field type
	 * @return	string
	 */
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
		$string = 'CREATE TABLE `' . $schema['tableSpec']['name'] . '` (';
		foreach ($schema['fieldSpec'] as $field => $data) {			
		
			if (isset($data['back']['precision'])) {
				$data['back']['size'] .= ',' . $data['back']['precision'];
			}
			
			if (isset($data['back']['size'])) {
				$string .= '  `' . $field . '` ' . $this->translateFieldTypes($data['back']['type']) . '(' . $data['back']['size'] . ')';
			} else {
				$string .= '  `' . $field . '` ' . $this->translateFieldTypes($data['back']['type']);
			}
			
			if (isset($data['back']['required']) && $data['back']['required'] == 'y') {
				$null = ' NOT NULL';
			} else {
				$null = ' NULL';
			}
			
			//var_dump($schema['tableSpec']['pk'][0]);
			//var_dump( $field);
			//echo '<br>';
			
			if (isset($schema['tableSpec']['auto']) && $schema['tableSpec']['pk'][0] == $field) {
				$string .= ' NOT NULL AUTO_INCREMENT';
			} else {
				$string .= $null;
			}
			
			$string .= ',';			
		}
		
		$string .= ' PRIMARY KEY `' . $schema['tableSpec']['pk'][0] . '` (`' . $schema['tableSpec']['pk'][0] . '`)';
		
		$string .= ');';
		//echo $string;
		return $string;
	}
	
}

?>