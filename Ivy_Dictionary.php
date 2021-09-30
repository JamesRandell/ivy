<?php
/**
 * Database module for IVY3.0.
 *
 * Contains various SQL-type commands to run against the chosen database. Manages the queries run on the database instance currently in use.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Database
 */
class Ivy_Dictionary {
	
	/**
	 * Contains meta data from the schema file
	 * 
	 * @access	public
	 * @var		array
	 */
	public $schema = array ();
	
	/**
	 * The database connection parameter
	 * 
	 * @access	public
	 * @var		object
	 */
	public $db;
	
	public $id;
	
	/*
	 * Tells the object if this is an extension and if so, the name of it
	 */
	public $extension = FALSE;
	
	/**
	 * Stores the current keys.  This includes both system (IVY) and application keys.
	 *
	 * @access private
	 * @var array
	 */
	private $keys = array ();
	
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
	public function __construct ($file, $specialArray = array ()) {

		(string) $database = ''; // the type of database
		(string) $file = strtolower($file);
		(array) $array = array ();
		
		$this->registry = $registry = Ivy_Registry::getInstance();
		$config = $registry->selectSystem('config');

		$this->error = Ivy_Error::getInstance();		

		$this->keys = $registry->selectSystem('keys');

		// put this here for temp as i have no idea how this works and will probably re-work it
		// Sept 2021
		define('EXTENSION', '');

		echo SITEPATH . '/site/' . SITE . '/model/'.$file.'.php';
		if (file_exists(SITEPATH . '/site/' . SITE . '/model/'.$file.'.php')) {

			require SITEPATH . '/site/' . SITE . '/model/'.$file.'.php';

		} else if (file_exists(SITEPATH . '/extension/' . EXTENSION . '/model/'.$file.'.php')) {

			require SITEPATH . '/extension/' . EXTENSION . '/model/'.$file.'.php';
			
			$array['tableSpec']['name'] = 'ivyext_' . EXTENSION . '_' . $array['tableSpec']['name'];
			$this->extension = EXTENSION;

		} else if (file_exists(SITEPATH . '/' . $file)) {

			require SITEPATH . '/' . $file;

		} else if (file_exists($file . '.php')) {	

			require $file . '.php';

		} else if (file_exists(IVYPATH . '/model/'.$file.'.php')) {

			require IVYPATH . '/model/'.$file.'.php';

		} else {

			die ('Model file missing: ' . $file);
			return FALSE;

		}

		if (empty($array['databaseSpec'])) {
			$array['databaseSpec'] = array ();
		}
		
		if (isset($array['databaseSpec']['type'])) {
			$database = $array['databaseSpec']['type'];
		} else {
			$database = $array['databaseSpec']['type'] = $config['db']['type'];
		}
		
		require_once IVYPATH . '/connections/' . $database . '.php';
		
		$this->db = call_user_func(array($database, 'getInstance'));		
		$this->db->connect($array['databaseSpec']);
		
		$session = $registry->selectSession();
		
		$array = $this->preserved_merge_array($array, $specialArray);
		
		$t['databaseSpec'] = (isset($array['databaseSpec']) ? $array['databaseSpec'] : array ());
		$t['tableSpec'] = (isset($array['tableSpec']) ? $array['tableSpec'] : array ());
		$t['fieldSpec'] = (isset($array['fieldSpec']) ? $array['fieldSpec'] : array ());
		
		
		if (!isset($array['tableSpec']['page'])) {
			$t['tableSpec']['page'] = 20;
		}
		
		/**
		 * do me a favor, CONVERT EVERYTHING TO A SINGLE CASE BEFORE DEBUGGING!!!!
		 */
		$t['tableSpec']['name'] = strtolower($t['tableSpec']['name']);

		(string) $table = $t['tableSpec']['name'];
		
		$form_field_encryption_key = $this->registry->selectSession('form_field_encryption_key');
		$form_field_encryption_key = 'supersecretkey';

		foreach ($array['fieldSpec'] as $field => $subArray) {

			/**
			 * A small loop here that deals with decrypting the form field keys.
			 * It's a nifty feature that allows us to encrypt user input field names from the View so that we don't
			 * expose any database table names via HTML. This loop just decrypts those field names if they exist,
			 * un-setting any originals in the process
			 */	
			if (!empty($_POST)) {
				foreach ($_POST as $key => $value) {

					if (($decrypted_key = openssl_decrypt($key, 'AES-256-CBC', trim($form_field_encryption_key))) !== FALSE) {

						if (mb_strtolower($table . '.' . $field) == mb_strtolower($decrypted_key)) {
							//$field = str_replace($table . '.', '', $decrypted_key);
							$_POST[ $field ] = $value;
							unset($_POST[ $key] );
						}
					}
				}
			}

			if (isset($array['fieldSpec'][$field]['back']['arraytouse'])) {
				if (isset($this->keys[ $subArray['back']['arraytouse'] ])) {
					$t['fieldSpec'][$field]['front']['option'] = $subArray['front']['option'] = $this->keys[ $subArray['back']['arraytouse'] ];
				}				
			}
			
			if (isset($array['fieldSpec'][$field]['join'])) {
				if ($this->extension) {					
					$t['fieldSpec'][$field]['join']['table'] = 'ivyext_' . $this->extension . '_' . $array['fieldSpec'][$field]['join']['table'];
				} else {
					$t['joinSpec'] = $this->_loadModelJoin($array['fieldSpec'][$field]['join']);
					//array_push($t['fieldSpec'], $this->_loadModelJoin($array['fieldSpec'][$field]['join'])); 
				}
			}
			
			
			
			if (!isset($t['fieldSpec'][$table.'.'.$field])) {
				$t['fieldSpec'][$table.'.'.$field] = $subArray;
				unset($t['fieldSpec'][$field]);
			}	

			if (isset($array['fieldSpec'][$field]['options'])) {
				$result = array ();
				$newTable = $table;
				if (isset($array['fieldSpec'][$field]['options']['ddl'])) {
					$newTable = $array['fieldSpec'][$field]['options']['ddl'];
				} else {
					$newTable = $array['fieldSpec'][$field]['options']['table'];
				}
				
				if (SITETYPE == 'ivyadmin') {
					$newTable = 'ivyext_' . $_GET['extension'] . '_' . $table;
				} else if (defined('EXTENSION') && strlen(EXTENSION) > 0) {
					$newTable = 'ivyext_' . EXTENSION . '_' . $table;
				} else if ($this->extension) {					
					$newTable = 'ivyext_' . $this->extension . '_' . $table;
				} else {
					$table = $table;
				}
				
				$key = $array['fieldSpec'][$field]['options']['key'];
				$value = $array['fieldSpec'][$field]['options']['value'];
				
				/*
				 * more than one field is specified, so get those columns and concatenate the result
				 */
				$tempValue = '';
				$originalValue = $value;
		
				if (is_array($value)) {											
					foreach ($value['fields'] as $optionField) {
						$tempValue .= $newTable . '.' . $optionField . ',';
					}
					$tempValue = substr($tempValue, 0, -1);
				} else {
					$tempValue = $value . ' AS value';
				}

				$value = $tempValue;
				
				(string) $where = '';
				
				if (isset($array['fieldSpec'][$field]['options']['where'])) {
					$where = ' WHERE ' . $array['fieldSpec'][$field]['options']['where'];
				}
				
				$order = $newTable . '.' . $key . ' ASC';
				if (isset($array['fieldSpec'][$field]['options']['order'])) {
					$order = $array['fieldSpec'][$field]['options']['order'];
				}
//echo 'SELECT ' . $newTable . '.' . $key . ',' . $value . ' FROM ' . $newTable . $where . ' ORDER BY ' . $newTable . '.' . $key . ' ASC';
				$tempResult = $this->db->query('SELECT ' . $newTable . '.' . $key . ',' . $value . ' FROM ' . $newTable . $where . ' ORDER BY ' . $order);
				#$result = $tempResult;

				foreach($tempResult as $row => $data) {
					$result[$data[$key]] = $data['value'];
					$y = $originalValue['format'];
					
					if (is_array($originalValue)) {
						foreach ($originalValue['fields'] as $optionField) {
							// check if the user has supplied the table name, if not - prefix each field with the table 
							$pos = strpos($y, '(?)');
							if ($pos !== false) {
								$y = substr_replace($y,$data[$optionField],$pos,strlen('(?)'));
							}
						}
						
						$result[$data[$key]] = $y;
					}
				}
				
				
				if (isset($array['fieldSpec'][$field]['options']['prepend'])) {
					$result = $array['fieldSpec'][$field]['options']['prepend']+$result;
				}
				
				if (isset($array['fieldSpec'][$field]['options']['append'])) {
					$result = $result+$array['fieldSpec'][$field]['options']['append'];
				}
				
				$t['fieldSpec'][$table.'.'.$field]['front']['option'] = $result;
			}

			if (isset($array['fieldSpec'][$field]['back']['rank'])) {
				if ($session['rank'] < $array['fieldSpec'][$field]['back']['rank']) {
					$t['fieldSpec'][$field]['front']['nobuild'] = true;
				}
			}

			if (isset($array['fieldSpec'][$field]['front']['rank'])) {
				if ($session['rank'] < $array['fieldSpec'][$field]['front']['rank']) {
					$t['fieldSpec'][$field]['front']['noview'] = true;
				}
			}


			/* 18/03/18 JR
			 * Added ACG and DCG to the model
			 */
			if (isset($array['fieldSpec'][$field]['back']['dcg'])) {
				$dcg = $array['fieldSpec'][$field]['back']['dcg'];
				//print_pre($session['acg']);
				
				if (empty(array_intersect($session['acg'], $dcg)) !== true) {
					$result = 'bahblah';
				}
			
			}
			
			
			if (isset($_POST[$field])) {
				#$t['data'][0][$field] = $_POST[$field];
			}	

			if (!isset($t['fieldSpec'][$table.'.'.$field])) {
				$t['fieldSpec'][$table.'.'.$field] = $subArray;
				unset($t['fieldSpec'][$field]);
			}
		}

		$this->schema = $t;
		$this->id = $registry->insertData($t);
	}
	
	private function _loadModelJoin ($joinSpec) {
	
		if (file_exists(SITEPATH . '/site/' . SITE . '/model/' . $joinSpec['table'] . '.php')) {

			require SITEPATH . '/site/' . SITE . '/model/' . $joinSpec['table'] . '.php';

			foreach ($joinSpec['fields'] as $field) {
				$result[$joinSpec['table'] . '.' . $field] = $array['fieldSpec'][$field];
			}
		return $result;
		}
	}
	
	public function __call ($name, $arguments) {
		
		return $this->data[0][$name];
		
	}
	
	protected function preserved_merge_array ($newArray, $otherArray) {
		if (!$otherArray) {
			return $newArray;
		}
		
		foreach( $otherArray as $key => $value)
		{
			if (!isset($newArray[$key])) {
				$newArray[$key] = array ();
			}
			
			if (!is_array($newArray[$key])) {
				$newArray[$key] = array ();
			}
			if (is_array($value)) {
				$newArray[$key] = $this->preserved_merge_array($newArray[$key], $value);
			} else {
				$newArray[$key] = $value;
			}
		}
		return $newArray;
	}

	/**
	 *	Translates any keys to the real values stored in a static array.
	 *
	 * Loads array.php file
	 * Loads the query result from the Registry
	 * Looks for any 'arraytouse' keys in the schema
	 * Looks for any keys in the data that matches a key in the array.php file
	 * Assigns the old value to a _ reserved key and replaces the original key with the translated key
	 *
	 * @param	number	$result		Holds the value of a query result set in the Registry
	 */
	protected function translateKeys () {
	
		$array = array ();
		
		$controller = (isset($_GET['controller']) ? $_GET['controller'] : 'index');

		
	
		$registry = Ivy_Registry::getInstance();

		$configArray = $registry->selectSystem('config');
		$queryArray = $registry->selectData($this->id);

		$configSystemArray = $configArray['system'];
		$unixDateFormat = $configSystemArray['unixformat'];
		
		if (empty($queryArray['data'])) {
			return;
		}

		foreach ($queryArray['data'] as $rowNo => $rowData) {
			foreach ($rowData as $field => $value) {
				
				
				/*
				 * Two if's going on here
				 * The first to find out if the $value has anything in it, if not default to 0
				 * Second, if the value is an array (for instance multple select elements have been passed by mistake)
				 * return the last value only
				 */
				$value = (($value) ? (is_array($value) ? array_pop($value) : $value) : 0);
				
				
				/**
				 * There are two replace commands in a MODEL file. The first runs a sub-query to get the data to replace the 
				 * original value, which is quite inefficiante but it does the job.
				 * The second way is this way, in which if you have a JOIN specified, you can take part or all of those fields 
				 * and replace the original value with them
				 */
				if (isset($queryArray['fieldSpec'][$field]['join']['replace'])) {

					$joinReplacement = $queryArray['fieldSpec'][$field]['join']['replace'];
					
					/**
					 * only bother with this is we have some fields to looks for
					 */
					if (isset($joinReplacement['fields'])) {

						// save the original value to an _ field
						$queryArray['data'][$rowNo]['_'.$field] = $value;
						$queryArray['data'][$rowNo][$field] = '';

						foreach ($joinReplacement['fields'] as $joinReplacementField) {
							// check if the user has supplied the table name, if not - prefix each field with the table 
							if (strpos($joinReplacementField, '.') === false) {
								$joinReplacementField = $queryArray['fieldSpec'][$field]['join']['table'] . '.' . $joinReplacementField;
							}

							$pos = strpos($joinReplacement['format'], '(?)');
							if ($pos !== false) {
								$joinReplacement['format'] = substr_replace($joinReplacement['format'],$rowData[$joinReplacementField],$pos,strlen('(?)'));
							}
						}
						$queryArray['data'][$rowNo][$field] = $joinReplacement['format'];
					}
				}


				if (isset($queryArray['fieldSpec'][$field]['back']['arraytouse'])) {
					$arraytouse = $queryArray['fieldSpec'][$field]['back']['arraytouse'];
					if (isset($this->keys[$arraytouse])) {
						
							$nValue = explode(',', $value);
								if (isset($nValue[1])) {
									// there are multiple elements in the array, so loop through them
									$queryArray['data'][$rowNo][$field] = '';
									foreach ($nValue as $valueKey) {
										$queryArray['data'][$rowNo][$field] .= $this->keys[$arraytouse][$valueKey] . ', ';
										$queryArray['data'][$rowNo]['_' . $field][$valueKey] = $valueKey;
									}

									$queryArray['data'][$rowNo][$field] = rtrim($queryArray['data'][$rowNo][$field], ', ');
								}
						if (isset($this->keys[$arraytouse][$value])) {
							$queryArray['data'][$rowNo]['_' . $field] = $value;
							$queryArray['data'][$rowNo][$field] = $this->keys[$arraytouse][$value];
						}
					}
				}
				if (isset($queryArray['fieldSpec'][$field])) {
					switch ($queryArray['fieldSpec'][$field]['back']['type']) {
						case 'unix'		:
							if (isset($queryArray['fieldSpec'][$field]['back']['format'])) {
								$unixDateFormat = $queryArray['fieldSpec'][$field]['back']['format'];
							}
							
							if (is_numeric($queryArray['data'][$rowNo][$field]) && strlen($queryArray['data'][$rowNo][$field]) == 10) {
								$queryArray['data'][$rowNo]['_'.$field] = $queryArray['data'][$rowNo][$field];
								$queryArray['data'][$rowNo][$field] = date($unixDateFormat, $queryArray['data'][$rowNo][$field]);

								if (date("Hi", $queryArray['data'][$rowNo]['_'.$field]) <= '0100') {
									$queryArray['data'][$rowNo][$field] = date($unixDateFormat, $queryArray['data'][$rowNo]['_'.$field]);
								}
							} else {
								$this->error->add(array('content' 	=>'Error converting unix timestamp to a human readable one',
														'field' 	=>$field,
														'id' 		=>$rowNo));
							}
							break;
						case 'datetime'		:

							if ( empty ( $value ) )
							{
								/* there is no data in the datetime, we'll return a blank entry as 
								otherwise we'll get an error from the framework */

								$queryArray['data'][$rowNo][$field] = '' ;
							}
							else
							{
							//echo $value;
								$myDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $value);
								$queryArray['data'][$rowNo]['_'.$field] = $myDateTime;
							
								if (isset($queryArray['fieldSpec'][$field]['back']['format'])) {
									try {

										$queryArray['data'][$rowNo][$field] = $myDateTime->format($queryArray['fieldSpec'][$field]['back']['format']);

									} catch (Exception $e) {
									
										$this->error->add(array('content' 	=>'Error converting SQL timestamp to a human readable one',
															'field' 	=>$field,
																'id' 		=>$rowNo));
									}
								} else {
									$queryArray['data'][$rowNo][$field] = $myDateTime->format('Y-m-d H:i:s');
								}
							}
							break;
					}
				}
				
			}
		}

		
		$registry->insertData($this->id, $queryArray);
	}
	
	
	
}
?>