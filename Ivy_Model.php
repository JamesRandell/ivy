<?php
/**
 * SVN FILE: $Id: Ivy_Database.php 17 2008-10-01 10:55:57Z shadowpaktu $
 * 
 * Project Name : Project Description
 *
 * @package className
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 17 $
 * @lastrevision $Date: 2008-10-01 11:55:57 +0100 (Wed, 01 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-01 11:55:57 +0100 (Wed, 01 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Database.php $
 */

class Ivy_Model extends Ivy_Dictionary {

	/**
	 * Contains reference to the Error object.
	 * 
	 * Other info goes here. ggf
	 * gfgfgfgf
	 * gfrtty
	 * 
	 * @access protected
	 * @var object
	 */
	protected $error;
	
	/**
	 * Contains reference to the Registry object.
	 *
	 * @access protected
	 * @var object
	 */
	protected $registry;
	
	/**
	 * Array that holds the current database schema.
	 *
	 * Includes things like the field specification, table specification and database specification.
	 *
	 * @access protected
	 * @var array
	 */
	public $schema = array ();
	
	public $id = 0;
	
	public $affected = 0;
	
	/**
	 * Array that just holds the fieldSpec from the schema.
	 *
	 * @access protected
	 * @var array
	 */
	protected $fieldSpec = array ();
	
	/**
	 * Holds any field names that we want to SELECT. If empty, then return everything
	 *
	 * @access private
	 * @var array
	 */
	private $field = array ();

	/**
	 * After a select has run, the results (if any) are stored in this variable
	 *
	 * @access public
	 * @var array
	 */
	public $data = array ();
	
	/**
	 * Temporary storage for query settings
	 *
	 * Accessed by Validation base class too for the Join method
	 *
	 * @access protected
	 * @var string
	 */
	protected $query = array ();
	
	public $limit = '';
	
	/**
	 * Turns pagination on or off
	 * 
	 * @access public
	 * @var bool
	 */
	public $pagination = true;
	
	public $order;
	/**
	 * Loads the table schema and db API.
	 *
	 * Table schema files are stored in the /mod directory. Depending on the table a specific db API
	 * is then called.
	 * Construct takes two parameters (first mandatory, second optional)
	 * First is the class file to load.  This contains information about the database, table and fields being called
	 * Second is an optional special rules array.  It contains settings that over-ride the default schema. It can also contain custom settings
	 * to add extra functionality to the schema (such as restricting the options to bring back on a select drop down
	 *
	 * @param	string		$file	Name of the table schema file to load.
	 * @param	array		$specialArray	Special rules to incoroporate to the schema
	 */
	public function __construct ($file, $specialArray = array ())
	{
		parent::__construct($file, $specialArray);
	}

/**
 * Holds the number of records found in the query
 * 
 * @access	public
 * @return	int
 */
public $count = 0;

	/**
	 * Runs a SELECT statement on the table.
	 *
	 * Specific by the $where string and restricted via the $fieldArray array.
	 * If no field array is supplied use the default one
	 * Checks fields if they exist in the table schema
	 * Looks for special table rules such as JOINS
	 * Build clauses
	 * Sets the front schema to the registry
	 * Returns the query ID
	 *
	 * @param	mixed	$where		The 'WHERE' part of an SQL query
	 * @param	array	$field		The fields that are required from the table in an array
	 * @return	int					stuff here
	 */
	public function select ($where = NULL, $fieldArray = array ())
	{
		$registry = Ivy_Registry::getInstance();
		if (is_array($where)) {
			if (isset($where['s'])) { // the key 's' means the value is an id, so lets search for it automatically
				$where = $this->schema['tableSpec']['pk'][0] . " = '" . $where['s'] . "'";
			} else if (isset($where[  $this->schema['tableSpec']['pk'][0] ])){
				$where = $this->schema['tableSpec']['pk'][0] . " = '" . $where[ $this->schema['tableSpec']['pk'][0] ] . "'";
			}
		} else if (is_int($where)) {
			$where = $this->schema['tableSpec']['pk'][0] . " = '" . $where . "'";	
		}
		//$this->query = array ();

		
		$tableName = (($this->schema['tableSpec']['name'] == '') ? '' : $this->schema['tableSpec']['name'] . '.');

		$this->query['from'] = (($this->schema['tableSpec']['name'] == '') ? "'" . $this->schema['databaseSpec']['server'] . "'" : $this->schema['tableSpec']['name']);

		if (!empty($fieldArray)) {
			$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));
			$fieldArray = $validation->fieldExists($fieldArray);

			
		
			$this->query['field'][$this->schema['tableSpec']['name']][ $this->schema['tableSpec']['pk'][0] ] = $tableName . $this->schema['tableSpec']['pk'][0];
			
			foreach ($fieldArray as $key => $field) {
				$this->query['field'][$this->schema['tableSpec']['name']][$field] = $field . ' AS "' . $field . '"';
				$schema['fieldSpec'][$field] = $this->schema['fieldSpec'][$field];
			}
			
			#unset($this->schema['fieldSpec']);
			#$this->schema['fieldSpec'] = $schema['fieldSpec'];

		} else if (!empty($this->query['field'])) {
			// we need this for some reason, must clean up. If a field array is passed in via the method (field), then process it here even though it's empty
			//$this->query['field'][$this->schema['tableSpec']['name']][$tableName . $this->schema['tableSpec']['pk'][0]] = $tableName . $this->schema['tableSpec']['pk'][0] . ' AS "' . $tableName . $this->schema['tableSpec']['pk'][0] . '"';

		} else {
			foreach ($this->schema['fieldSpec'] as $field => $value) {
				if (isset($value['back'])) {
					$fieldArray[$field] = $field;
				}
			}

			$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));
			$fieldArray = $validation->fieldExists($fieldArray);

			foreach ($fieldArray as $field => $value) {
				if ($tableName . $this->schema['tableSpec']['pk'][0] !== $field) {
					//if (isset($this->schema['databaseSpec']['type']) && $this->schema['databaseSpec']['type'] == 'mysql') {
						//$realField = str_replace('.', '`.`', "`$field`");
					//} else {
						$realField = str_replace('.', '"."', '"' . $field . '"');
					//}
				
					$this->query['field'][$this->schema['tableSpec']['name']][$field] = $realField . ' AS "' . $field . '"';
				}
			}
		}

		unset($fieldArray);

		$this->field();

		
		$this->join();
		$this->replace();

		$fields = $this->combineFields();

		$query = "SELECT $fields FROM " . $this->query['from'];

		if (!empty($this->db->parameter)) {

			$query .= " WHERE ";
			$parameterLength = count($this->db->parameter) -1;

			// sorts the where parameters by there level of paranthesis
			usort($this->db->parameter, function($a, $b) {
				return $a['level'] <=> $b['level'];
			});

			$whereLevel = (int) 0;

			foreach ($this->db->parameter as $key => $value) {

				/**
				 * fix a strange issue where additional VALUE keys appears in the parameter array.
				 * so we run a check to see if the FIELD key exists as well
				 */
				if (isset($value['field'], $value['operator'], $value['logic'])) {

					/**
					 * Look at the level of the WHERE (parenthesis to group where clauses)
					 */
					if ($value['level'] <> $whereLevel) {
						$whereLevel = $value['level'];
						$where .= '(';
					}

					/**
					 * if the WHERE parameter has a dot (.) in it, which means the user is specifying a table name and a field, then wrap quotes around it so the DB
					 * engine understands
					 */
					$value['field'] = (strpos($value['field'], '.') === false) ? $value['field'] : str_replace('.', '"."', $value['field']);

					/**
					 * we now check is there the operator is IS so we can replace the parameters with NULL/NOT NULL
					 */
					if ($value['operator'] == 'IS' OR $value['operator'] == 'IS NOT') {
						$where .= '"' . $value['field'] . '" ' . $value['operator'] . ' ' . $value['value'];

						// we also unset this item from the parameters so it doesn't interfere with other (?). Otherwise it will still be there
						// with a full number of question marks (?), but we've now replaced one with an actual value so it will miscount
						unset($this->db->parameter[$key]);

					//} else if ($value['operator'] == 'LIKE') {

						/**
					 	 * check to see if there is a like operator so we can insert the % at the end of the search string.
					 	 * DJL (david.lodwig@curtisfitchglobal.com) - 07/12/17.
					 	 */

						//$where .= '"' . $value['field'] . '" ' . $value['operator'] . ' ' . $value['value'] . '%' ;

						// we also unset this item from the parameters so it doesn't interfere with other (?). Otherwise it will still be there
						// with a full number of question marks (?), but we've now replaced one with an actual value so it will miscount
						//unset($this->db->parameter[$key]);
					} else {
						$where .= '"' . $value['field'] . '" ' . $value['operator'] . ' (?)';
					}
					
					// close the current where group if the level changes, reset back to 0 for the next loop
					if ($value['level'] <> $whereLevel) {
						$whereLevel = 0;
						$where .= ')';
					}

					// right now the only option is OR in a group of where clauses
					if ($whereLevel > 0 && $key < $parameterLength) {
						$where .= ' OR ';
					}

					if ($whereLevel === 0 && $key < $parameterLength) {
						$where .= ' ' . $value['logic'] .' ';
					}

					// close the final group if we're on the last iteration and we have a level greater than 0
					if ($whereLevel > 0 && $key === $parameterLength) {
						$where .= ')';
					}
				}
			}

			$query .= $where;

		} else if ($where) {
			/* updated where handling allows the script calling script to pass an array (ie $_POST or $_GET).
			This part loops through the array (if present) and uses only the elements that are part of the fieldSpec
			- stripping out things like the submit button or any custom fields */
			if (is_array($where)) {
				$s = '';
				foreach ($where as $k => $v) {
					if (empty($v)) {
						continue;
					}
					
					$tempFieldVar = str_replace($this->schema['tableSpec']['name'].'_', $this->schema['tableSpec']['name'].'.', $k);
					
					if (isset($this->schema['fieldSpec'][$tempFieldVar])) {
						$s .= " $k = '$v' AND";
					}
				}
				if (!empty($s)) {					
					$s = rtrim($s, ' AND');
					$query .= " WHERE $s";
				}
			} else {
				$query .= " WHERE $where";
			}
		}


		if (strlen($this->order) > 0) {
			$query .= ' ORDER BY ' . $this->order;
		}
		
		if ($this->pagination !== false) {
			$query = $this->pagination($query, array(	
														'from'			=>	$this->query['from'],
														'where'			=>	$where,
														'databaseSpec' 	=> array (
															'type' 			=> $registry->system['config']['db']['type']
														)
													)
										);
		}
		
		/**
		 * result is the ID of the query result
		 */
		 
		$result = $this->db->query($query); 

		if ($result === true) {
			return $this->id;
		}
		
		$resultArray = $this->schema;
		$resultArray['data'] = $result;

		$registry->insertData($this->id, $resultArray);

		$this->translateKeys();
		$data = $registry->selectData($this->id);

		$data = $data['data'];
		
		$this->data = $data;

		$this->schema['data'] = $data;

		$this->db->parameter = array ();
		return $this->id;
	}
	
	

	/**
	 * Allows the user to create a WHERE clause using the DBMS or data sources safe way of parsing user input
	 *
	 * @datemodified	04/04/2017
	 * @author 	James Randell <james.randell@curtisfitchglobal.com>
	 */
	public function where ($field, $value, $operator = '=', $logic = 'AND', $level = 0) {

		if (strpos($field, '.') === false) {
			$field = $this->schema['tableSpec']['name'] . '.' . $field;
		}

		/*
		 * if we have a LIKE operator then wrap it with some SQL based chars to indicate a wildcard search
		 */
		if ($operator == 'LIKE') {
			if (substr($operator, 0, 1) !== '%') {
				$value = '%' . $value;
			}

			if (substr($operator, -1, 1) !== '%') {
				$value = $value . '%';
			}
		}

		$e = $this->db->parameter[] = array (
									'table'		=>	$this->schema['tableSpec']['name'],
									'field'		=>	$field,
									'value'		=>	$value,
									'operator'	=>	$operator,
									'logic'		=>	$logic,
									'level'		=>	$level);

		return $this;
		
	}

	/**
	 * Creates an ORDER BY bit, appends a property which is then used by the SELECT method
	 *
	 * @datemodified	04/04/2017
	 * @author 	James Randell <james.randell@curtisfitchglobal.com>
	 */
	public function order ($field, $direction = 'ASC') {
		
		if (!array_key_exists($field, $this->schema['fieldSpec'])) {

			$field = $this->schema['tableSpec']['name'] . '.' . $field;

			if (!array_key_exists($field, $this->schema['fieldSpec'])) {
				$field = '';
			}
		}
		
		if (!empty($field)) {
			switch ($direction) {
				case 'DESC'	:	$direction = 'DESC';
								break;
				default 	:	$direction = 'ASC';
			}
			$this->order = '"' . $field . '" ' . $direction;
		}

		return $this;
	}

	/**
	 * Only returns the specified fields in ths array when passed to a SELECT statement
	 *
	 * @datemodified	04/04/2017
	 * @author 	James Randell <james.randell@curtisfitchglobal.com>
	 */
	public function field ($fieldArray = array()) {

		$tableName = (($this->schema['tableSpec']['name'] == '') ? '' : $this->schema['tableSpec']['name']);

		//$this->query['field'][$tableName]['_PK'] = $tableName . '.' . $this->schema['tableSpec']['pk'][0] . ' AS "' . $tableName . '.' . $this->schema['tableSpec']['pk'][0] . '"';
		//$this->query['field'][$tableName]['_PK'] = $tableName . '.' . $this->schema['tableSpec']['pk'][0] . ' AS "_PK"';
		$this->query['field'][$tableName][$tableName . '.' . $this->schema['tableSpec']['pk'][0]] 
			= $tableName . '.' . $this->schema['tableSpec']['pk'][0] . ' AS "' . $tableName . '.' . strtolower($this->schema['tableSpec']['pk'][0]) . '"';

		foreach ($fieldArray as $key => $value) {
			$this->query['field'][$tableName][$tableName . '.' . $value]
				 = $tableName . '.' . $value . ' AS "' . $tableName . '.' . $value . '"';
		}

		return $this;
			
	}

	/**
	 * Specifies the number of records to return in our query
	 *
	 * In reality is mearly alters the LIMIT clause, so we will always look at the entire table, just only return the pages 
	 * with the specified number of records on it back
	 *
	 * @datemodified	06/04/2017
	 * @author 			James Randell <james.randell@curtisfitchglobal.com>
	 * @return 			object
	 */
	public function limit ($rows) {

		$this->limit = $rows;

		return $this;
	}

	public function count ($where = NULL)
	{
		if ($where) {
			$where = ' WHERE ' . $where;
		}
		$query = 'SELECT COUNT(*) FROM ' . $this->schema['tableSpec']['name'] . $where;
		#echo $query;
		$result = $this->db->query($query); /* result is the ID of the query result */

		
		return $result[0]['COUNT(*)'];
	}
	
	
	/**
	 * Runs a specifc UPDATE type query depending on the $dataArray passed.
	 *
	 * @param	array	$dataArray	An array of 'keys => value' pairs for an update query to run.
	 */
	public function update ($dataArray = array ())
	{
		$registry = Ivy_Registry::getInstance();
		$this->query['type'] = 'update';
		
		if (empty($dataArray)) {
			if (empty($_POST)) { 
				return false;
			} else {
				$dataArray = $_POST;
			}
		}

		$dataArray = $this->_fixFieldNames($dataArray);

		if (!isset($this->schema['tableSpec']['pk'])) {
			return false;
		}

		$this->schema['tableSpec']['name'] . '.' . $this->schema['tableSpec']['pk'][0];

		if (empty($dataArray[ $this->schema['tableSpec']['name'] . '.' . $this->schema['tableSpec']['pk'][0] ])) {
		
			//if (empty($dataArray[ $this->schema['tableSpec']['pk'][0] ])) {
				die ('UPDATE query Missing PK: ' . $this->schema['tableSpec']['pk'][0]);
				return false;
		
			//}
		
		}

		$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));
	
		$dataArray = $validation->check($dataArray);

		if ($validation->error !== false) {
			return false;
		}
		


		$string = '';
		
		foreach ($dataArray as $field => $value) {
			
			
			/**
			 * updated 27th May 2010
			 * 
			 * if the value is 0 bytes long, and a date, then change it from a literal string
			 * to a null value. This fixes all the 0's appearing in the place of dates when we 
			 * update a record without actually changing the date field
			 */
			if ($this->schema['fieldSpec'][$field]['back']['type'] == 'unix' && strlen($value) === 0) {
				$this->db->parameter[] = array (
									'field'		=>	$field,
									'value'		=>	null);
				$field = str_replace('.', '"."', '"' . $field . '"');
				$string .= $field." = (?), ";

			} else if (!isset($this->schema['fieldSpec'][$field]['back']['auto'])) {
				$this->db->parameter[] = array (
									'field'		=>	$field,
									'value'		=>	$value);
				$field = str_replace('.', '"."', '"' . $field . '"');
				$string .= $field." = (?), ";
			}

			
		}
		$string = rtrim($string, ', ');


		$this->db->parameter[] = array (
									'field'		=>	$this->schema['tableSpec']['pk'][0],
									'value'		=>	$dataArray[$this->schema['tableSpec']['name'] . '.' . $this->schema['tableSpec']['pk'][0]]);

		$query = 'UPDATE '.$this->schema['tableSpec']['name'].' SET '.$string.' WHERE ' . $this->schema['tableSpec']['pk'][0] . '=(?)';

		$error = $this->error->check();
		
		if (isset($_GET['debug'])) {
			echo $query.'<br><br><br>';
			$this->db->parameter = array ();
		} else {
			if ($error === false) {
				if ($this->db->query($query) === false) {
					$this->db->parameter = array ();
					return false;
				};

				$this->db->parameter = array ();
				return true; 
			} else {
				$t['error'] = $registry->selectError('validation');

				$this->db->parameter = array ();
				return false;
			}

			$this->db->parameter = array ();
		}

		$this->db->parameter = array ();
	}
	
	/**
	 * Attempts run an INSERT statement on a database.
	 *
	 * Checks the schema rules for any errors with the data
	 * Builds the SQL statement
	 * Runs the SQL
	 * Puts a status message in the Registry.
	 *
	 * @param	array	$dataArray	An array of 'keys => value' pairs for an update query to run.
	 */
	public function insert ($dataArray = array ())
	{
		$registry = Ivy_Registry::getInstance();
		$this->query['type'] = 'insert';

		if (empty($dataArray)) {
			if (empty($_POST)) {
				return FALSE;
			} else {
				$dataArray = $_POST;
			}
		}

		$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));


		$dataArray = $this->_fixFieldNames($dataArray);
		$dataArray = $validation->check($dataArray);

		$this->data = $dataArray;

		#$schema = $this->registry->selectData($this->id);

		$this->schema['data'][0] = $dataArray;
		$this->data[0] = $dataArray;
		$a = $this->schema;
		$a['data'][0] = $dataArray;

		if ($validation->error !== false) {
			return false;
		}
		
		
		if ($this->error->check() === false) {// if FALSE there are no errors
			#echo 'This query can run';

		} else { #echo 'This query is not correct!';
			$registry->insertData($this->id, $a);
			$t['error'] = $registry->selectError('validation');
			#echo '<pre>';
			#print_r($t['error']);
			#echo '</pre>';
			#return false;
		}
		
		$fieldList = '';
		$valueList = '';
		
		foreach ($dataArray as $field => $value) {
			#if (!in_array($field, $schema['tableSpec']['pk'])) {
				if ($this->schema['tableSpec']['pk'][0] == $field) {
					#continue;
				}
				
				if (isset($this->schema['fieldSpec'][$field]['back']['auto'])) {
					continue;
				}
				
				if (isset($this->schema['databaseSpec']['type']) && $this->schema['databaseSpec']['type'] == 'mysql') {
					//$field = str_replace($this->schema['tableSpec']['name'] . '.', '', "`$field`");
				} else {
					$field = str_replace('.', '"."', '"' . $field . '"');
				}
				$fieldList .= $field.',';
				#$valueList .= "'".$value."',";

				$valueList .= ($value == NULL ? 'NULL,' : "'" . $value . "',");
			#}
		}
		$fieldList = rtrim($fieldList, ',');
		$valueList = rtrim($valueList, ',');
		
		
		if (isset($this->schema['databaseSpec']['type']) && $this->schema['databaseSpec']['type'] == 'mysql') {
			$query = 'INSERT INTO ' . $this->schema['tableSpec']['name'] . ' ('.$fieldList.') VALUES ('.$valueList.')';
		} else {
			$query = 'INSERT INTO "' . $this->schema['tableSpec']['name'] . '" ('.$fieldList.') VALUES ('.$valueList.')';
		}


	
		
		
			$result = $this->db->query($query);
			
			if ($result !== FALSE) {

				/**
				 * checks the result to get the ID of the inserted record. Hopefully our database connector
				 * implements a GET INSERTED ID check or something like it. This returns what that number is
				 */
				if(is_numeric($result)) {
					$this->id = $result;
				}
				
				$this->schema['data'] = $result;
				$registryCounter = $registry->insertData($this->schema);
				return TRUE;
			}
		
		return FALSE;

	}

	
	/**
	 * Attempts run an DELETE statement on a database.
	 *
	 * This method takes an array of keys that have to match what is required in the table spec.  If the query
	 * doesn't pass all of the $pk's then the method quits with that $pk is missing.
	 *
	 * @param	array	$dataArray	An array of 'keys => value' pairs for an update query to run.
	 */
	public function delete ($dataArray = array ())
	{
		(string) $where = '';
		(string) $pk = $this->schema['tableSpec']['pk'];
		
		if (empty($dataArray)) {
			if (empty($_POST)) { 
				return FALSE;
			} else {
				$dataArray = $_POST;
			}
		} else if (is_numeric($dataArray)) {
			$tempDataArray = $dataArray;
			unset($dataArray);
			$dataArray[ $pk[0] ] = $tempDataArray;
		} else if (is_string($dataArray)) {
			$where = $dataArray . ' AND ';
		}
		
		if (!is_string($dataArray)) {
			foreach ($this->schema['tableSpec']['pk'] as $row => $pk) {
				if (!isset($dataArray[$pk])) {
					die ('Not enough criteria were met when deleting records.  Please make sure you have included keys: ' . $pk);
				}
				$where .= "$pk = '" . $dataArray[$pk] . "' AND ";
			}
		}
		$where = rtrim($where, ' AND ');
		
		$table = $this->schema['tableSpec']['name'];
		$query = 'DELETE FROM ' . $table . ' WHERE ' . $where;
	


		$this->db->query($query);
		
		return TRUE;
		
	}
	
	
	public function query ($query)
	{
		$registry = Ivy_Registry::getInstance();
		$result = $this->db->query($query);
		
		$schema = $registry->selectData($this->id);
		
		$resultArray = $schema;
		$resultArray['data'] = $result;


		$this->registry->insertData($this->id, $resultArray);

		
		$this->translateKeys();
		$data = $this->registry->selectData($this->id);
		$data = $data['data'];
		
		$this->data = $data;
		return $this->id;
	
	}
	/**
	 * Attempts run an EXPLAIN statement on a database.
	 *
	 * Runs explian on the selected table then returns the result
	 *
	 * @return	array
	 */	 
	public function explain ($q)
	{
		$query = $q;#"EXPLAIN global_user";
		return $this->db->query($query);
	}
	
	public function sql ($query)
	{
		$this->data = $this->db->query($query);
		
		$registry = Ivy_Registry::getInstance();
		$data = $this->schema;
		$data['data'] = $this->data;
		$registry->insertData($this->id, $data);

		$this->translateKeys();
		
		$result = $registry->selectData($this->id);
		
		
		$this->data = $result['data'];
		

		return $data;
		
	}
	
/**
 * Enumerate
 *
 * This tests a table connection, and returns information about it
 */
public function enumerate () {

	$result = array ();


	return $this->db->connect();

}
	
	
/**
 * describe a table structure
 */
public function desc ()	{
	$t = $this->db->query('DESCRIBE ' . $this->schema['tableSpec']['name']);
		
	if (!$t) {
		return false;
	}		$this->data = $t;
		return $t;
	}
	
	
/**
 * Creates a new table
 */
public function create () {
	return $this->createTable(true);
}
	
public function createTable ($run = FALSE) {
	$t = $this->schema;
	foreach ($t['fieldSpec'] as $field => $value) {

		$realFieldName =  str_ireplace($this->schema['tableSpec']['name'] . '.', '', $field);

		if (strpos($realFieldName, $this->schema['tableSpec']['name'] . '.') === false) {
		//	$realFieldName = $this->schema['tableSpec']['name'] . '.' . $realFieldName;
		}

		$t['fieldSpec'][$realFieldName] = $value;
		unset($t['fieldSpec'][$field]);
	}
		
	(string) $string = $this->db->createTable($t);

	if ($run == TRUE) {
		if ($this->db->query($string) === FALSE) {
			return FALSE;
		}
		return TRUE;
	} else {
		echo $string;
	}
}
		
	
/**
 * Updates an existing table
 */
public function updateTable () {}
	
/**
 * Deletes a table
 */
public function deleteTable () {}
	
/**
 * Truncatea a table
 */
public function truncateTable () {}

public function truncate () {
	$this->db->query('TRUNCATE TABLE ' . 
		$this->schema['tableSpec']['name']);
}
	
public function drop () {
	$this->db->query('DROP TABLE "' . $this->schema['tableSpec']['name'] . '"');
	return $this;
}
	
	private function join () {
		
		$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));
		
		if (is_array($join = $validation->sJoin('join'))) {
		
			/**
			 * If we have a join then blank out the FROM part of our query ready for new join syntax
			 */
			$this->query['from'] = '';
			
			/**
			 * holds the model file specified in by the JOIN statement in the parent models fieldSpec
			 */
			(array) $array = array ();



			foreach($join as $field => $data) {

				if (!isset($data['table'])) {

					foreach ($data as $k => $sData) {
						$sData['fieldParent'] = $field;
						$sData['multiple'] = true;
						$join[$k] = $sData;
					}

					unset($join[$field]);

				} else {

					$join[$field]['fieldParent'] = $field;
					$join[$field]['multiple'] = false;

				}

			}

			foreach ($join as $alias => $joinSpec) {
				$field = $joinSpec['fieldParent'];
				
				
				if (file_exists(SITEPATH . '/site/' . SITE . '/model/' . $joinSpec['table'] . '.php')) {
					require SITEPATH . '/site/' . SITE . '/model/' . $joinSpec['table'] . '.php';
				} else if (strpos($joinSpec['table'], 'ivy_') !== false) {
					require 'core/model/' . $joinSpec['table'] . '.php';
				} else if (file_exists('dictionary/table_' . $joinSpec['table'] . '.php')) {
					require 'dictionary/table_' . $joinSpec['table'] . '.php';
				} else if (file_exists('model/' . $joinSpec['table'] . '.php')) {
					require 'model/' . $joinSpec['table'] . '.php';
				} else if ($this->extension) {
					$explode = explode('_', $joinSpec['table']);
					require 'extension/' . $this->extension . '/model/' . $explode[2] . '.php';
				}

$arrayOriginal = $array;

				if (isset($joinSpec['fields'])) {
					$tempArray = array ();
					$findArray = array ();
					$replaceArray = array ();
					
					foreach ($joinSpec['fields'] as $joinField) {
						$array['fieldSpec'][$joinField]['front']['type'] = 'hidden'; /* change the type to hidden so when used in a form, 
							it doesn't come back as a visible field */
						
						$tempArray['fieldSpec'][$joinField] = $array['fieldSpec'][$joinField];
						$findArray[$joinField] = $joinField;

						if ($explode) {
							/*
							 * This removes the duplicate field in the query field array on an extension.
							 * For some reason it doesn't remove itself on an extension (?)
							 */
							unset($this->query['field'][ $this->schema['tableSpec']['name'] ][$joinField]);
						}
						
						//$replaceArray[$joinField] = $joinSpec['table'] . '.' . $joinField;
						$replaceArray[$joinField] = $joinField;

					}
					$array = $tempArray;
					
				}

				
				
				$fieldOriginal = $field;
				foreach ($array['fieldSpec'] as $field2 => $data2) {
					if ($joinSpec['multiple'] === true) {
						$array['fieldSpec'][$alias . '.' . $field2] = $data;
					} else {
						$array['fieldSpec'][$joinSpec['table'] . '.' . $field2] = $data;
					}
					unset($array['fieldSpec'][$field2]);
				}
				
				unset($array['fieldSpec'][$field]);
				
				$this->schema['fieldSpec'] = array_merge($this->schema['fieldSpec'], $array['fieldSpec']);


				//(string) $fieldT = ($field[0] == '_') ? ltrim($field, '_') : $field;
				
				$joinSpec['pk'] = isset($joinSpec['pk']) ? $joinSpec['pk'] : $joinSpec['key'];
				
				$joinSpec['type'] = isset($joinSpec['type']) ? $joinSpec['type'] : NULL;
				switch ($joinSpec['type']) {
					case 'left'		:	$joinType = ' LEFT JOIN ';
										break;
					case 'right'	:	$joinType = ' RIGHT JOIN ';
										break;
					default			:	$joinType = ' JOIN ';
				}

				/**
				 * JOIN syntax differs slightly after the first join. This just adds the table name on the first
				 * join but not subsequent ones
				 */
				if (empty($this->query['from'])) {
					$this->query['from'] = $this->schema['tableSpec']['name'];//. ' ' . $joinSpec['type'];
				}
				
				$this->query['from'] .=  			
						$joinType . $joinSpec['table'] . ' ' . ($joinSpec['multiple'] === true ? '"' . $alias . '"' : '') . 
						' ON ' . $fieldOriginal . 
							' = ' . ($joinSpec['multiple'] === true ? $alias : $joinSpec['table']) . '.' . $joinSpec['pk'];

				if (isset($joinSpec['where'])) {
					
					foreach ($joinSpec['where'] as $clauseKey => $clauseValue) {
						
						if (!$clauseValue['operator']) {
							$clauseValue['operator'] = '=';
						}

						$this->db->parameter[] = array (
									'table'		=>	($joinSpec['multiple'] === true ? $alias : $joinSpec['table']),
									'field'		=>	($joinSpec['multiple'] === true ? $alias : $joinSpec['table']) . '.' . $clauseValue['field'],
									'value'		=>	$clauseValue['value'],
									'operator'	=>	$clauseValue['operator'],
									'logic'		=>	' AND '
									
						);
					}
				}

				foreach ($array['fieldSpec'] as $field => $value) {
					if ($joinSpec['multiple'] === true) {
						$this->query['field'][$alias][$field] = $field . ' AS "' . $field . '"';
					} else {
						$this->query['field'][$joinSpec['table']][$field] = $field . ' AS "' . $field . '"';
					}
				}
			}
		}
	}
	
	
	private function replace ()
	{
		$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));
		
		if (is_array($join = $validation->sJoin('replace'))) {

			foreach ($join as $field => $joinSpec) {
				$this->schema['fieldSpec']['_' . $field] = $this->schema['fieldSpec'][$field];
				$tempArray = array ();
				$findArray = array ();
				$replaceArray = array ();
				
				foreach ($joinSpec['fields'] as $replaceField) {
					$findArray[$replaceField] = $replaceField;
					$replaceArray[$replaceField] = "'||" . $joinSpec['table'] . '.' . $replaceField . "||'";
					$replaceArray[$replaceField] = "'||" . $replaceField . "||'";
				}
				

				$findArray[$field] = $field;

				$replaceArray[$field] = "'+" . $joinSpec['table'] . '.' . $field . "+'";

				$r = str_replace($findArray, $replaceArray, $this->schema['fieldSpec'][$field]['replace']['format']);
				$r = ltrim($r, "'||");
				
				$r .= "'";
				$r = rtrim($r, "||''");
				
				
					(string) $fieldT = ($field[0] == '_') ? ltrim($field, '_') : $field;
				
				
				#if (isset($fieldArray[$this->schema['tableSpec']['name'] . '.' . $field])) {
					$this->query['field'][$this->schema['tableSpec']['name']]['_' . $field] = $this->schema['tableSpec']['name'] . '.' . $fieldT . ' as "_' . str_replace('"', '', $field) . '"';
					
					
					if (isset($this->schema['fieldSpec'][$field]['replace']['where'])) {
						$this->query['field'][$this->schema['tableSpec']['name']][$field] = '(select ' . $r . ' from ' . $joinSpec['table'] . ' where ' . $joinSpec['table'] . '.' . $joinSpec['key'] 
							. ' = ' . $this->schema['tableSpec']['name'] . '.' . $field . ' AND ' . $this->schema['fieldSpec'][$field]['replace']['where'] . ') as "' . str_replace('"', '', $field) . '"';
					} else {
						echo $this->query['field'][$this->schema['tableSpec']['name']][$field] = '(select ' . $r . ' from ' . $joinSpec['table'] . ' where ' . $joinSpec['table'] . '.' . $joinSpec['key'] 
							. ' = ' . $fieldT . ') as "' . str_replace('"', '', $field) . '"';
					}
				#}
			}
		}
	}
	
	
	/**
	 * Merges the field array together
	 *
	 * Takes the query multi dimensional array and merges it.  Loops through the tables, then the fields.
	 */
	private function combineFields ()
	{
		$string = '';
		foreach ($this->query['field'] as $table => $data) {
			$string .= implode(', ', $data);
			$string .= ',';
		}
		$string = rtrim($string, ',');

		return $string;	
	}
	
	/**
	 * Adds in pagination
	 * @param	string	$query		The full query
	 * @param	array	$array		Holds the FROM and WHERE query parameters
	 */
	private function pagination ($query, $array = array ())
	{		
		$rowsPerPage = (!empty($this->limit) ? $this->limit : $this->schema['tableSpec']['page']);		

		$where = (isset($array['where'][1]) ? ' WHERE ' . $array['where'] : '');

		$queryCount = $this->db->query('SELECT COUNT(*) as "COUNT(*)" FROM ' . $array['from'] . ' ' . $where);



		
		$numRows = (isset($queryCount[0]) ? $queryCount[0]['COUNT(*)'] : 0);
		
		$this->count = $numRows;
		
		if ($this->schema['tableSpec']['page'] > 0) {
			// find out the lastpage if the rowsPerPage is greater than zero
			$lastPage = ceil($numRows/$rowsPerPage);
		} else {
			$lastPage = 1;
		} // if
		
		if ($numRows <= $rowsPerPage) {
			#echo '<br><br>'.$numRows.'-'.$query;
			// there are less records than can go on a single page, so get them all
			$pageNo = 1;
			$firstRecord = 1;
			$lastRecord = $pageNo * $rowsPerPage;
		} else {
			// there are more records to be split over multiple pages, lets run our query for those.

			if (isset($pageNo) && $pageNo <= '1') {
			// if there is only one page
				$pageNumber = 1;
			} elseif (isset($pageNo) && $pageNo > $lastPage) {
				// then pageNumber var must equal the last page
				$pageNo = $lastPage;
			} // if
			if (isset($_GET['page']) && $_GET['page']!='') {
				// is there a page number in the $_GET array?
				$pageNo = $_GET['page'];
			} else {
				$pageNo = 1;
			} // if
			
			// set up the first and last record variables for our query
			$firstRecord = (($pageNo-1) * $rowsPerPage) +1;	
			$lastRecord = $pageNo * $rowsPerPage;
			
			
			$parameterArray['query'] = $query;
			$parameterArray['first'] = $firstRecord;
			$parameterArray['last'] = $lastRecord;
			$parameterArray['pk'] = $this->schema['tableSpec']['pk'][0];
			$parameterArray['table'] = strtolower($this->schema['tableSpec']['name']);
			$parameterArray['order'] = (($this->order[1]) ? ' ORDER BY ' . $this->order : 'ORDER BY "' . $parameterArray['table'] . '.' . $parameterArray['pk'] . '"');
			//$parameterArray['order'] = (($this->order[1]) ? ' ORDER BY ' . $this->order : 'ORDER BY "' . $parameterArray['pk'] . '"');
			
			$pagination = $this->db->pagination($parameterArray);
				
			if ($pagination !== false) {
				$query = $pagination;
			} else {
				if ($array['databaseSpec']['type'] == 'mysql') {
					$query = $query . ' LIMIT ' . ($firstRecord-1) . ', '
						 . (($lastRecord+1) - $firstRecord);
				} else {
					$query = "SELECT * FROM 
						(SELECT a.*, RowNum as \"_RNUM\"
						FROM ($query) a
						WHERE RowNum <= $lastRecord)
						WHERE $firstRecord <= \"_RNUM\"";
				}
			}
		}

		$this->schema['page']['pageno'] = $pageNo;
		$this->schema['page']['pagelast'] = $lastPage;
		$this->schema['page']['pagefirstrecord'] = $firstRecord;
		$this->schema['page']['pagelastrecord'] = $lastRecord;
		$this->schema['page']['recordcount'] = $numRows;
		
		return $query;
	}
	
	
	public function validate ($dataArray) {
	
		$registry = Ivy_Registry::getInstance();
		$this->query['type'] = 'update';

		if (empty($dataArray)) {
			if (empty($_POST)) {
				return false;
			} else {
				$dataArray = $_POST;
			}
		}

		
		
		$validation = new Ivy_Validation($this->schema, array('query'=>$this->query));

		$dataArray = $validation->check($dataArray);
		$this->data = $dataArray;

		#$schema = $this->registry->selectData($this->id);

		$this->schema['data'][0] = $dataArray;
		$this->data[0] = $dataArray;
		$a = $this->schema;
		$a['data'][0] = $dataArray;

		if ($this->error->check() === false) {// if FALSE there are no errors
			//echo 'This query can run';
			return true;

		} else { #echo 'This query is not correct!';
			$registry->insertData($this->id, $a);
			$t['error'] = $registry->selectError('validation');
			
			return false;
		}
		
		
	}
	

/**
 * Takes the dataArray, usually from a form submission and corrects any 
 * underscoares to dots. Also prefixes the table name if it's missing
 * @param	array	$dataArray		A form submission
 */
private function _fixFieldNames ($dataArray) {

	(string) $realFieldName = '';
	
	
	foreach ($dataArray as $field => $value) {

		$realFieldName =  str_ireplace($this->schema['tableSpec']['name'] . '_', $this->schema['tableSpec']['name'] . '.', $field);

		if (strpos($realFieldName, $this->schema['tableSpec']['name'] . '.') === false) {
			$realFieldName = $this->schema['tableSpec']['name'] . '.' . $realFieldName;
		}

		$newArray[$realFieldName] = $value;
	}
	
	return $newArray;
}


/**
 *	__destruct.
 *
 * closes the current database connection.
 */
public function __destruct () {
	if ($this->db) {
		$this->db->disconnect();
	}
}	
	
	
}
?>