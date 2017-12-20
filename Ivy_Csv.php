<?php
/**
 * Csv module for IVY3.0
 *
 * @author James Randell
 * @version 0.1
 * @package Csv
 */
 
/**
 * @package Csv
 */
class Ivy_Csv extends Ivy_Dictionary
{

	
/**
 * Calls the parent constructor and ammends the schema array.
 * 
 * Invokes the Dictionary constructor to retrieve meta data. Pushes a new field
 * on the fieldSpec array which works as the primary key for any data retreived.
 * 
 * @param	string	$file			Name of the schema file to load
 * @param	array	$specialArray	Values to overload schema values
 * @created	17 February 2009
 */
public function __construct ($file, $specialArray = array ())
{
	parent::__construct($file, $specialArray);
	
	$this->schema['fieldSpec']['_PKID'] =  array (
			'front'		=> array (
				'title'		=>	'ID',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'text',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		);
	
	$this->schema['tableSpec']['pk'] =  array ('_PKID');
}

/**
 * Selects lines from a CSV file
 * 
 * Select works the same way as a Database select in that it can do a basic
 * where search through a csv file but it does accept what fields to bring out.
 * 
 * @param	array|string|int	$where	Search clause
 * @param	array	$fieldArray	limit the result to these fields only
 * @created	17 February 2009
 */
public function select ($where, $fieldArray = array ())
{
	(array) $numericField = array ();
	(int) $i = 0;

	/*
	 * easier to have the field name as the key
	 */
	$fieldArray = array_flip($fieldArray);

	/*
	 * go through the fieldSpec so we get numbered fields instead of names. This
	 * is so that we can match up the schema t othe data we pull from the CSV
	 * file.
	 */
	foreach ($this->schema['fieldSpec'] as $field => $data) {
		if (!empty($fieldArray)) {
			if (isset($fieldArray[$field])) {
				$numericField[$i] = $field;
			}
		} else {
			$numericField[$i] = $field;
		}
		++$i;
	}

	$i = 0;
	$handle = fopen($this->schema['databaseSpec']['server'], "r");
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);

		
		for ($c=0; $c < $num; $c++) {
			if (isset($numericField[$c])) {
				$array[$i][ $numericField[$c] ] =  $data[$c];
			}
		}
		
		$array[$i]['_PKID'] = $i;
		++$i;
	}
	
	

	/*
	 * if a where clause was supplied, check for the default ID value (s) and
	 * only return that row.
	 */
	if (is_array($where)) {
		if (isset($where[ $this->schema['tableSpec']['pk'][0] ])) {
			$tempArray = $array[ $this->schema['tableSpec']['pk'][0] ];
			$array = array ();
			$array[0] = $tempArray;
		} else if (isset($where['s'])) {
			$tempArray = $array[ $where['s'] ];
			$array = array ();
			$array[0] = $tempArray;
		}
	}

	fclose($handle);

	$this->data = $array;
	return $array;
}
	
	
}
?>