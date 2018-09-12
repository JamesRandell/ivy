<?php
/**
 * SVN FILE: $Id: Ivy_Validation.php 18 2008-10-01 11:01:03Z shadowpaktu $
 *
 * Project Name : Project Description
 *
 * @package className
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 18 $
 * @lastrevision $Date: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Validation.php $
 */
class Ivy_Validation {


	private $schema = array ();
	private $query;

/**
 * public error variable to store generated errors (if any)
 * 
 * @access	public
 * @type	bool|array
 */
public $error = false;
	
	public function __construct ($schema, $other = array ()) {
		// schema
		$this->schema = $schema;
		
		// other array such as the query array
		$this->query = (isset($other['query']) ? $other['query'] : array ());
		
		$this->registry = Ivy_Registry::getInstance();
	}
	
	/**
	 * Checks if the field supplied by the client exists in the schema.
	 *
	 * @param	array	$fieldArray		Contains an array of client supplied fields
	 * @result	array
	 */
	public function fieldExists ($fieldArray) {
		
		#$this->error = Ivy_Error::getInstance();


		
		foreach ($fieldArray as $no => $field) {
			if (!isset($this->schema['fieldSpec'][$field])) {
				#$this->error->add(array('type'		=>	'Field missing',
					#					'msg'		=>	'The field "'.$field.'" does not exist'));

				/**
				 * also check if it exists with the table name prefixed
				 */
				if (!isset($this->schema['fieldSpec'][ $this->schema['tableSpec']['name'] . '.' . $field])) {
					unset($fieldArray[$no]);
				} else {
					$fieldArray[$no] = $this->schema['tableSpec']['name'] . '.' . $field;
				}
			}
		}
		
		return $fieldArray;
	}
	
	/**
	 * Looks for any special rules in the fieldspec that may affect how a query is run.
	 *
	 * @result	array
	 */
	public function sJoin ($rule) {
		foreach ($this->query['field'][ $this->schema['tableSpec']['name'] ] as $field => $value) {
			if (isset($this->schema['fieldSpec'][$field][$rule])) {
				
				$result[$field] =  $this->schema['fieldSpec'][$field][$rule];
			}
		}
		if (isset($result)) {
			return $result;
		}
		
	}
	

	/**
	 * Checks if the field supplied by the client exists in the schema.
	 *
	 * @param	array	$fieldArray		Contains an array of client supplied fields
	 * @result	array
	 */
	public function check ($dataArray) {
		#$this->error = Ivy_Error::getInstance();
		// Loops through the fields
		
		$result = array ();

		foreach ($dataArray as $field => $value) {
			if (isset($this->schema['fieldSpec'][$field]) && $field[0] != '_') {
				$dataArray[$field] = str_replace (  "'", '&#039;', $dataArray[$field] );
      			#$dataArray[$field] = str_replace (  '"', '&quot;',$dataArray[$field] );
				
				if (isset($this->schema['fieldSpec'][$field]['options']['validate'])) {
					$optionsKey = $this->schema['fieldSpec'][$field]['options']['key'];
					$optionsValue = $this->schema['fieldSpec'][$field]['options']['value'];
					$optionsTable = $this->schema['fieldSpec'][$field]['options']['table'];
					
					$temp = new Ivy_Model('table_' . $optionsTable);
					$temp->select($optionsKey . " = '$value'", array($optionsValue));
					
					if (isset($temp->data[0])) {
						$value = $temp->data[0][$optionsValue];
					} else {
						$this->registry->insertError(array('field'		=>	$field,
												'title'		=>	$this->schema['fieldSpec'][$field]['front']['title'],
												'spec'		=>	'validate',
												'property'	=>	'y',
												'value'		=>	$dataArray[$field],
												'msg'		=>	'The code you selected was not found. Please use the search provided and try again.',
												'type'		=>	'validation'));
					}
					
				}
				if (isset($this->schema['fieldSpec'][$field]['back']['ignoreupdate'])) {
					#unset($this->schema['fieldSpec'][$field]);
				}
				
				foreach ($this->schema['fieldSpec'][$field]['back'] as $attribute => $value) {

					$method = 'errorCheck_' . strtolower(ucfirst($attribute));		
					
					if (is_array($dataArray[$field])) {
						$dataArray[$field] = implode(',', $dataArray[$field]);
					}

					# if the value of the attribute is 'n' for no then don't call this, otherwise
					# we end up trying to validate attributes that are turned off.
					# DJL 13/07/17
					if ( $value != 'n' AND $value != 'N' ) {
						//$field = strtolower($field);
						$returnedValue = self::$method($value, $dataArray[$field], $this->schema['fieldSpec'][$field]['front']['title'], strtolower($field));

						// Has an error been returned?
						if (isset($returnedValue['error'])) {
							$this->registry->insertError(
							
								$ttt = array('field'	=>	strtolower($field),
									'title'		=> 	$this->schema['fieldSpec'][$field]['front']['title'],
									'spec'		=>	$attribute,
									'property'	=>	$value,
									'value'		=>	$dataArray[$field],
									'msg'		=>	$returnedValue['error'],
									'type'		=>	'validation'));
							
								$this->error[] = $ttt;

								#echo $field . $returnedValue['error'];

								#unset($result[$field]);# = $dataArray[$field];
						} else if (isset($returnedValue['value'])) {
							// ok then a value?
							$result[$field] = $returnedValue['value'];

						/*
						 * we also fool assign the returned value to the local
						 * var, so the next part of this if isn't Run on
						 * subsequent loops throug the attributes
						 */
						$dataArray[$field] = $returnedValue['value'];
					
					
					
						} else {
							/*
							 * if not a value or an error, then nothing so select
							 * the initial field value
							 */
							$result[$field] = $dataArray[$field];
						}		
					
					} else {
						
						/*
						 * do this as the flag was set to N
						 * if not a value or an error, then nothing so select
						 * the initial field value
						 */
						
						$result[$field] = $dataArray[$field];
						
					}
				
				}
			}
		}

		return $result;
	}
	
	/**
	 * Validates the data against various flags set by the schema
	 *
	 * @param	string		$option		the type of data thats expected.
	 */
	private function errorCheck_type ($option, $value) {
		switch ($option) {
			case 'char'	:
			
				break;

			case 'varchar'	:
			case 'nvarchar' :
			case 'var'		:
			case 'longtext' :
				
				// Added some filtering to remove HTML entities
				// and special chars from text fields.
				 $value = htmlentities($value , ENT_COMPAT, ini_get("default_charset") , FALSE );
                 $value = htmlspecialchars($value , ENT_COMPAT, ini_get("default_charset") , FALSE );
                 $value = str_replace("(","&#40;",$value);
                 $value = str_replace(")","&#41;",$value);

				return array('value'=>trim($value));
				break ;

			case 'integer'	:
			case 'tinyint' 	:
			case 'bigint'	:
			case 'int'		:
				
				if ($value == 0) {
					return array('value'=>NULL);
				}
				if (strlen($value) >= 1 && is_numeric($value) == FALSE) {
					return array('error'=>"A numeric value is required",
									'value'=>$value);
				} else {
					return array('value'=>$value);
				}
				break;

			case 'decimal'	:
				
				if ($value == 0) {
					return array('value'=>NULL);
				}
				if (strlen($value) >= 1 && is_numeric($value) == FALSE) {
					return array('error'=>"A decimal value is required",
									'value'=>$value);
				} else {
					return array('value'=>$value);
				}
				break;
			
			case 'double'	:
			case 'float'	:
				
				if ($value == 0) {
					return array('value'=>NULL);
				}
				if (strlen($value) >= 1 && is_numeric($value) == FALSE) {
					return array('error'=>"A float value is required",
									'value'=>$value);
				} else {
					return array('value'=>$value);
				}
				break;
			
			case 'unix'	:
				
				if (strlen($value) == 0) {
					return array('value'=>$value);
				}

				if (strlen($value) == 10 && is_numeric($value)) {
					return array('value'=>$value);
				}

				if (strtotime($value) === FALSE) {
					return array('error'=>"The value can't be converted to a date",
									'value'=>$value);
				}

				return array('value'=>strtotime($value));
				break;
			
			case 'date'		:

				if (($timestamp = strtotime($value)) === false) {
					
					return array('error'=>"The value can't be converted to a date",
									'value'=>$value);
				} else {
					$date = date_create($value);
					
					return array('value'=>date_format($date, 'Y-m-d'));
				}
				
				break;

			case 'datetime'	:
				
				if (($timestamp = strtotime($value)) === false) {
					
					return array('error'=>"The value can't be converted to a date",
									'value'=>$value);
				} else {
					$date = date_create($value);
					
					return array('value'=>date_format($date, 'Y-m-d H:i:s'));
				}
				
				break;
		}
	}
	
	/**
	 * If the value exceeds the option, then truncate the rest of the string
	 *
	 * @param	int		$option		Integer with length to truncate to
	 * @param	string	$value		The string to be checked for truncation
	 * @result	array
	 */
	private function errorCheck_truncate ($option, $value, $title, $field) {
		if (strlen($value) > $option) {
			return array('value'=>substr_replace($value, '', $option));
		}		
	}
	
	private function errorCheck_ignoreupdate ($option, $value) {	
		return;
	}


	
	/**
	 * Checks a the size of $value and returns an error msg if it exceeds the size set in $size.
	 *
	 * @param	int		$option		Integer with a maximum size limit
	 * @param	string	$value		The value to be checked
	 * @result	array
	 */
	private function errorCheck_size ($option, $value) {
		if (strlen($value) > $option) {			
			return array('error'=>"This value can't be more than $option characters");
		}
	}
	
	
	/**
	 * Checks a the size of $value and returns an error msg if it goes below the size set in $size.
	 *
	 * @param	int		$option		Integer with a minimim size limit
	 * @param	string	$value		The value to be checked
	 * @result	array
	 */
	private function errorCheck_minsize ($option, $value) {
		if (strlen($value) < $option) {			
			return array('error'=>"This value can't be less than $option characters");
		}
	}
	
	private function errorCheck_max ($option, $value) {
		if ($value > $option) {			
			return array('error'=>"This value can't be higher than $option");
		}
	}
	
	/**
	 * Looks at the database for any other values that are the same, validates a unique row when inserting
	 */
	private function errorCheck_unique ($option, $value, $title, $field) {
		if (isset($this->query['type']) && $this->query['type'] == 'insert') {
			$obj = new Ivy_Model($this->schema['tableSpec']['name']);
			$result = $obj->db->query('select count(' . $field . ')  as "0"
				from ' . $this->schema['tableSpec']['name'] . ' WHERE ' . $field . " = '$value'");
			if ($result[0][0] != 0) {
				return array('error'=>'This has already been taken, please choose another');
			}
		}
		return array('value'=>$value);
	}
	
	
	/**
	 * Runs the regex code for the field
	 *
	 * @param	int		$option		Integer with a minimim size limit
	 * @param	string	$value		The value to be checked
	 * @result	array
	 */
	private function errorCheck_regex ($option, $value) {
		preg_match($option, $value, $match);

		if (count($match) == 4) {
			return array('value'=>$value);
		}
		
		if (count($match) >= 1) {
			return array('value'=>$value);
		}
		
		return array('value'=>$value,
					'error'=>"This value is processed, and must match the correct output.");

	}
	
	/**
	 * Converts all alpha-numeric chars to uppercasae.
	 *
	 * @param	int		$option		
	 * @param	string	$value		The string to be converted.
	 * @result	array
	 */
	private function errorCheck_uppercase ($option, $value) {
		return array('value'=>strtoupper($value));
	}
	

	/**
	 * Produces an error msg if there is no value.
	 *
	 * @param	int		$option
	 * @param	string	$value		The value to be checked
	 * @param	string	$title		Front facing title of the field
	 * @result	array
	 */
	private function errorCheck_required ($option, $value, $title) {
		if ($option == 'y') {
			if (strlen($value) == 0) {
				$title = strtolower($title);
				$a = 'a';
				$vowel = array ('a', 'e', 'i', 'o');
				foreach ($vowel as $letter) {
					if ($title[0] == $letter) {
						$a = 'an';
					}
				}
				
				return array('error'=>"Please enter $a $title");
			}
		} else {
			return array('value'=>$value);
		}
	}
	
	/**
	 * This double validates the value given with a value in an array or database.  
	 * This is mainly used for when an AJAX type search is used, instead of a standard select drop down.
	 *
	 * @param	int		$option
	 * @param	string	$value		The value to be checked
	 * @param	string	$title		Front facing title of the field
	 * @result	array
	 */
	private function errorCheck_validate ($option, $value, $title) {	
		return array('value'=>$value);
	}
	
	/**
	 * Algorithm check - Modulus 11 algorithm
	 *
	 * @param	int		$option
	 * @param	string	$value		The value to be checked
	 * @result	array
	 */
	private function errorCheck_algorithm ($option, $value) {	
		switch ($option) {
			case 'modulus11'	:
				$value = str_replace(' ', '', $value);

				$checkdigit = substr($value, -1);
				
				$count = strlen($value) - 1;
				$factorBase = 10;
				(array) $array = array ();
				
				if ($value == 0) {
					return array('error'=>'This number is not a valid number');
				}
				
				for ($i = 0; $i < $count; $i++) {
					$factor = $factorBase - $i;

					// step 1 - multiply each digit by its weighing factor
					$array[$i] = $value[$i] * $factor;
				}
				
				/*
				 * step 2 - add the results together
				 */ 
				$sum = array_sum($array);
				
				/*
				 * step 3 - divide the total by 11 and establish the remainder
				 * 
				 * we also explode the second part after the decimal to get a whole number, then insert a 
				 * decimal after the first number to create a decimal (out of the decimal), and round it 
				 * up to get our check digit
				 */ 
				$sum = $sum / 11;
				
				$temp = explode('.', $sum);				
				$remainder = ceil(substr($temp[1], 0, 1) . '.' . substr($temp[1], 1));
				
				/*
				 * do some checks
				 */
				if ($remainder == 0 && $checkdigit == 0) {
					// this is ok, to handle 0s
					return true;
				} else if ($remainder == 1) {
					return array('error'=>'This number is not a valid number');
				} else if ($remainder == 0 && $checkdigit != 0) {
					// if the remainder is 0 and the check is any other value then it's wrong
					return array('error'=>'This number is not a valid number');
				}
				
				// step 4- subtract the remainder from 11 to get the check digit
				$result = 11 - $remainder;

				if ($result == $checkdigit) {
					// check digit matches result, so it's correct
					return true;
				} else {
					return array('error'=>'This number is not a valid number');
				}
				
			break;
		}
	}
	
	
	/**
	 * Will convert the key of an array to its key => value pairs.
	 *
	 * @param	int		$option		Integer with a maximum size limit
	 * @param	string	$value		The value to be checked
	 * @result	array
	 */
	private function errorCheck_arraytouse ($option, $value) {}
	
	private function errorCheck_auto ($option, $value) {}
	
	/**
	 * Takes the default value and assigs a var to it
	 *
	 * @param	int		$option		
	 * @param	string	$value		The string to be converted.
	 * @result	array/void
	 */
	private function errorCheck_default ($option, $value) {
		switch ($option) {
			case 'collar'	:
				if (empty($value)) {
					$session = $this->registry->selectSession(0);
					return array('value'=>(isset($session['collar']) ? $session['collar'] : 'Guest User'));
				} else {
					return array('value'=>$value);
				}
				break;
			case 'uuid'	:
				if (empty($value)) {
					return array('value'=>sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
					       mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
					       mt_rand(0, 65535), // 16 bits for "time_mid"
					       mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
					       bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
					           // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
					           // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
					           // 8 bits for "clk_seq_low"
					       mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node" 
					   ));
				} else {
					return array('value'=>$value);
				}
				break;
			case 'date'	:
				if (empty($value)) {
					return array('value'=>time());
				} else {
					return array('value'=>$value);
				}
				break;
			case 'datetime'	:
				if (empty($value)) {
					return array('value'=>time());
				} else {
					return array('value'=>$value);
				}
				break;
			case 'bcrypt'	:
				if (empty($value)) {
					$options = array(
						'cost'	=>	12
					);
					
					return array(
						'value'	=> password_hash(time(), PASSWORD_DEFAULT, $options));
				} else {
					//return array('value'=>$value);
					
					$options = array(
						'cost'	=>	12
					);
					
					return array(
						'value'	=> password_hash($value, PASSWORD_DEFAULT, $options));
				}
				break;
			case 'random'	:
				if (empty($value)) {
					return array(
						'value'	=> dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)));
				} else {
					return array('value'=>$value);
				}
				break;
			default		:
				if (!isset($value[0])) {
					return array('value'=>$option);
				} else {
					return array('value'=>$value);
				}
				break;
		}
	}
	
	
	/**
	 * Checks the rank of the field, if the user rank is lower than this value
	 * the field wont be passed back for the UPDATE/INSERT
	 *
	 * @param	int		$option		
	 * @param	string	$value		The string to be converted.
	 * @result	array/void
	 */
	private function errorCheck_rank ($option, $value) {
		$session = $this->registry->selectSession(0);

		if ($session['rank'] >= $value) {
			return array('value'=>$value);
		}
	}
	
	/**
	 * Used for DECIMAL, FLOAT etc field types for precision
	 *
	 * @param	int		$option		
	 * @param	string	$value		The string to be converted.
	 * @result	array/void
	 */
	private function errorCheck_precision ($option, $value) {
		if (!is_numeric($value)) {			
			return array('error'=>"This value needs to be numeric");
		}
		
		$dotPos = strpos($value, '.');
		$valueLength = (strlen((string)$value) - $dotPos);
		
		//is there a dot?
		if ($dotPos > 0) {
			$valueLength--;	
		}
		
		if ($valueLength > $option) {
			return array('value'=>round($value, $option));
			//return array('error'=>"Precision is $option while value length is $valueLength");
		}
	}
	
	private function errorCheck_format ($option, $value) {
	
		return array('value'=>$value);
		
	}
	
	/*
	 * Looks at the join attribute to see if it's members exists.
	 *
	 * @param	int		$option
	 * @param	string	$value
	 * @result	array
	 */
	private function join ($option, $value) {
		if (is_array($option)) {
			if (!isset($option['table']) || !isset($option['pk'])
			 || !isset($option['fields']) || !isset($option['format'])) {
			 	return array('error'=>"There are sub-keys missing in the fieldSpec -> join");
			}
		}
	}
	
}
?>
