<?php

class Ivy_Html
{
	public $data = '';
	
	
	public function __construct () {}
	
	public function select ($query)
	{
		(array) $array = array ();
		(int) $i = 0;
		(object) $doc = new DOMDocument();
		(object) $doc->loadHTML($this->data);

		$xpath = new DOMXpath($doc);
		$elements = $xpath->query($query);

		if (!is_null($elements)) {
			
			foreach ($elements as $node) {
				
				$result[$i]['data'] = $node->nodeValue;
				foreach ($node->attributes as $index=>$attr) { 
					$result[$i]['meta'][ $attr->name ] = $attr->value;
				} 

				++$i;
				
			}
		}
		
		return $result;
	}
	
	
}

?>