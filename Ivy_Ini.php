<?php
/**
 * SVN FILE: $Id: Ivy_Ini.php 18 2008-10-01 11:01:03Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Ini.php $
 */
class Ivy_Ini extends Ivy_Dictionary {

	public function __construct ($file, $specialArray = array ())
	{
		parent::__construct($file, $specialArray);
	}
	
	

	public function select ()
	{		
		$registry = Ivy_Registry::getInstance();
		
		(string) $name = (isset($this->schema['tableSpec']['name']) ? $this->schema['tableSpec']['name'] : '');
		$result = $this->db->load($this->schema['databaseSpec']['server'], $name);
		
		$resultArray = $this->schema;
		$resultArray['data'] = $result;
		
		$registry->insertData($this->id, $resultArray);
		
		$data = $registry->selectData($this->id);
		$data = $data['data'];
		
		$this->data = $data;
		return $this->id;
	}

	/**
	 * Updates the current INI file
	 *
	 * Selects all content from the INI file
	 * Merges the altered data ($data) with the existing data in the file
	 * saves the data to the existing file, overwritting it's contents
	 *
	 * @param	array	$data		The data to be updated, usually a $_POST array
	 */
	public function update ($data = array ())
	{
		(string) $string = '';
		
		if (empty($data)) {
			if (empty($_POST)) { 
				return FALSE;
			} else {
				$data = $_POST;
			}
		}
		
		$result = $this->db->load($this->schema['databaseSpec']['server']);
		
		
		$registry = Ivy_Registry::getInstance();
		
		$schema = $registry->selectData($this->id);
		
		$validation = new Ivy_Validation($this->schema);
			
		$data = $validation->check($data);
	
		$tempData[0] = $data;
		unset($data);
		$data = $tempData;


		
		$array = $this->preserved_merge_array($result, $data);

		foreach ($array[0] as $header => $data) {
			#$string .= (empty($header) ? '' : "[$header]\n");
			#foreach ($data as $key => $value) {
				$string .= "$header = $data\n";
			#}
			$string .= "\n";
		}

		$error = $this->error->check();

		if ($error === FALSE) {
			if (file_put_contents($this->schema['databaseSpec']['server'], $string) === FALSE) {
				trigger_error('The file could not be written to: ' . $this->schema['databaseSpec']['server']);
				return FALSE;
			}		
			return TRUE; 
		} else {
			return FALSE;
		}
	}

	
	/**
	 * Alias for update
	 */
	public function insert ($data = array ())
	{
		return $this->update ($data);
	}
}
?>