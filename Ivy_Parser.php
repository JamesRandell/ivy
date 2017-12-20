<?php
/**
 * SVN FILE: $Id: Ivy_Parser.php 18 2008-10-01 11:01:03Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Parser.php $
 */
class Ivy_Parser
{
	/**
	 * file name.
	 *
	 * @access private
	 * @var string
	 */
	private $fileName = '';
	
	/**
	 * file data.
	 *
	 * @access private
	 * @var array
	 */
	public $fileData = array ();

	/**
	 * Chooses a method appropriate for the file type being parsed.
	 *
	 * @param	string		$file	name of the file to be parsed.
	 */
	public function __construct ($file)
	{
		$this->fileName = $file;
		$filenameArray = explode('.', $this->fileName);
		$fileExtension = strtoupper($filenameArray[1]);
		
		switch ($fileExtension) {
			case 'INI'	:	$this->parse_INI(); break;
		};
	}
	
	/**
	 * Parses an .ini file.
	 */
	private function parse_INI ()
	{
		$array = parse_ini_file($this->fileName, true);
		$this->fileData = $array;	
		$this->data = $array;	
	}
	
	/** 
	 * Saves the result of the parse to the data store.
	 */
	public function save ()
	{
		$this->dataStore = datastore::getInstance();
		
		foreach ($this->fileData as $key => $value) {
			$this->dataStore->insertData($key, $value);
		}
	}
	
	/**
	 * Returns the contents of the parsed file back to the calling script.
	 *
	 * @return	array
	 */
	public function load ()
	{
		return $this->fileData;
	}
}

?>