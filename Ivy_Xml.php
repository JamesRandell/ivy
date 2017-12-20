<?php
/**
 * SVN FILE: $Id: Ivy_Xml.php 18 2008-10-01 11:01:03Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Xml.php $
 */
class Ivy_Xml extends Ivy_Dictionary
{
	/**
	 * holds the current and single instance of this object
	 * @access private
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Will contain presentation data once methods have been called
	 * @access public
	 * @var array
	 */
	public $data;
	
	/**
	 * counter is incremented by the method logger()
	 * @access private
	 * @var integer
	 */
	private $counter = 0;	
	
	/**
	 * Id of the current data in the registry
	 * @access public
	 * @var integer
	 */
	public $id = 0;
	
	
	public function __construct ($file = NULL, $specialArray = array ())
	{
		if ($file) {
			parent::__construct($file, $specialArray);
		}
	}
	
	
	/**
	 * Always returns the same instace of this object
	 * @return	instance		instace of the parent
	 */
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new Ivy_Xml;
		}
		
		return self::$instance;
	}
	
	public static function load ($path)
	{
		return $xml = simplexml_load_file($path);

	}
	
	public function write ($prm_rootElementName, $prm_xsltFilePath = NULL)
	{
		#$registry = Ivy_Registry::getInstance();
		#$array = $registry->selectSystem();
		
		$this->openMemory();
        $this->setIndent(true);
        $this->setIndentString(' ');
        $this->startDocument('1.0', 'UTF-8');

        $this->startElement($prm_rootElementName);
		
	
	}
	
	
	/**
	 * Set an element with a text to a current xml document.
	 *
	 * @param string $prm_elementName An element's name
	 * @param string $prm_ElementText An element's text
	 * @return null
	*/ 
	private function setElement($prm_elementName, $prm_ElementText){
		$this->startElement($prm_elementName);
		$this->text($prm_ElementText);
		$this->endElement();
	}

	/**
	 * Construct elements and texts from an array.
	 *
	 * The array should contain an attribute's name in index part
	 * and a attribute's text in value part.
	 *
	 * @param array $prm_array Contains attributes and texts
	 * @return null
	*/ 
	public function fromArray($prm_array){
		foreach ($prm_array as $index => $text){
			if(is_array($text)){
				$this->fromArray($text);
			} else {
				$this->setElement($index, $text);
			}
		}
	}

	/**
     * Return the content of a current xml document.
     * @access public
     * @param null
     * @return string Xml document
     */ 
    public function getDocument(){
        $this->endElement();
        $this->endDocument();
        return $this->outputMemory();
    }

    /**
     * Output the content of a current xml document.
     * @access public
     * @param null
     */ 
    public function output(){
        file_put_contents('data.xml',  $this->getDocument());
    }
	




	public static function toArray ($xmlString, $get_attributes=0)
	{
		(object) $xml_parser = xml_parser_create();		
		(array) $valueArray = array ();
		(array) $indexArray = array ();

		xml_parse_into_struct($xml_parser, $xmlString, $valueArray, $indexArray);
		xml_parser_free($xml_parser);
		
		$params = array();
		$level = array();
		foreach ($valueArray as $data) {
		  if ($data['type'] == 'open') {
			  $level[$data['level']] = strtolower($data['tag']);
		  }
		  if ($data['type'] == 'complete') {
			$start_level = 1;
			$php_stmt = '$params';

			$data['value'] = (isset($data['value']) ? $data['value'] : '');

				
			$data['tag'] = strtolower($data['tag']);
			while($start_level < $data['level']) {
			  $php_stmt .= '[$level['.$start_level.']]';
			  $start_level++;
			}
			$php_stmt .= '[$data[\'tag\']] = $data[\'value\'];';
			eval($php_stmt);
		  }
		}

		if (isset($params['root'])) {
			return $params['root'];
		} else {
			return array ();
		}
	}
	
	
	function toXml($array, $level=1) {
         $xml .= "\n<root>\n";

    foreach ($array as $key => $value) {
        $key = strtolower($key);
    	if (is_numeric($key)) { $key = '_'.$key;}
        if (is_array($value)) {
            $multi_tags = false;
            foreach($value as $key2=>$value2) {
			
                if (is_array($value2)) {
                    $xml .= str_repeat("\t",$level)."<$key>\n";
                    $xml .= $this->toXml($value2, $level+1);
                    $xml .= str_repeat("\t",$level)."</$key>\n";
                    $multi_tags = true;
                } else {
                    if (trim($value2)!='') {
                        if (htmlspecialchars($value2)!=$value2) {
                            $xml .= str_repeat("\t",$level).
                                    "<$key><![CDATA[$value2]]></$key>\n";
                        } else {
                        	if (is_numeric($key2)) { $key2 = '_'.$key2;}
                            $xml .= str_repeat("\t",$level).
                                    "<$key2>$value2</$key2>\n";
                        }
                    }
                    $multi_tags = true;
                }
            }
            if (!$multi_tags and count($value)>0) {
                $xml .= str_repeat("\t",$level)."<$key>\n";
                $xml .= $this->toXml($value, $level+1);
                $xml .= str_repeat("\t",$level)."</$key>\n";
            }
        } else {
            if (trim($value)!='') {
            	if (htmlspecialchars($value)!=$value) {
                    $xml .= str_repeat("\t",$level)."<$key>".
                            "<![CDATA[$value]]></$key>\n";
                } else {
                    $xml .= str_repeat("\t",$level).
                            "<$key>$value</$key>\n";
                }
            }
        }
    }

	$xml .= "</root>\n";

    return $xml;
}

	
	
	
	
	/**
	 * Takes the ID, gets the array from the registry and then converts it to XML.
	 *
	 * @param		string	$id		The id of the array in the registry
	 * @param		array		$parts	Specific parts of the dataset like 'data' or 'fieldSpec'.
	 * @return
	*/
	public function insert ($id, $parts = array ('data'))
	{	
		$registry = Ivy_Registry::getInstance();
		$registryArray = $registry->selectData($id);
		
		foreach ($parts as $key) {
			$array[$key] = $registryArray[$key];
		}
		
		$id = $registry->insertData($this->toXml($array));
		$this->id = $id;
		return $id;
	}
}

function ArrayToXML($array) {
    $node = 0;
    foreach($array as $k => $v) {
        $tag = ( (empty($k) || is_numeric($k) ) ? 'row' : $k);
       
    	if (htmlspecialchars($v)!=$v && !is_array($v)) {
                    $v = "<![CDATA[$v]]>";
    	}
        $xml .= '<' . $tag . '>' . (is_array($v) ? ArrayToXML($v) : $v) . '</' . $tag . '>';
        $node++;
    }
    return $xml;
}




?>