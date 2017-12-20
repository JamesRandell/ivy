<?php
/**
 * CMS module for IVY3.0.
 *
 * Content Management Module for IVY3.0
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Navigation
 */
 
class api_Controller extends admin {

protected $title = 'API';

public function __construct ()
{
	
	parent::__construct();
	
	if (isset($_GET['site'])) {
		$primaryFolders = Ivy_File::selectFile('site/'.$_GET['site'].'/controller');
		
		foreach ($primaryFolders as $key => $value) {
			if ($value == '.svn') { continue; }
			$nav[ $value ]['title'] = ucfirst($value);
			$nav[ $value ]['query'] = 's=' . $value;
			
		}

		$this->context = $nav;
		return;
	}
	
	$primaryFolders = Ivy_File::selectFile('core');
	
	foreach ($primaryFolders as $key => $value) {
		if ($value == '.svn') { continue; }
		$nav[ $value ]['title'] = ucfirst($value);
		$nav[ $value ]['query'] = 's=' . $value;
		
	}

	

	$this->context = $nav;
}
public function _base () 
{
	
}

/**
 * parses a document string looking for a tag
 * 
 * This method breaks up a multi-line comment in to its component parts, then
 * loops through each line looking for a tag.
 */
private function getDocComment ($string, $tag)
{
	(array) $matches = array ();
	(int) $i = 0;
	
	/*
	 * split up the comment in to an array of lines
	 */
	preg_match_all("/".$tag."(.*)(\\r\\n|\\r|\\n)/U", $string, $matches);
	
	
	foreach ($matches as $id => $data) {
		
		/*
		 * remove empty lines that just contain asterisks (*)
		 */
		foreach ($data as $key => $value) {
			$matches[ $id ][ $key ] = trim($matches[ $id ][ $key ]);
			if (empty($matches[ $id ][ $key ])) {
				unset($matches[ $id ][ $key ]);
			}
		}
		
		if (empty($matches[ $id ])) {
			unset($matches[ $id ]);
			continue;
		}
		
		/*
		 * now we look for specific tags so we can parse that line according to
		 * how it is supposed to be formatted
		 */
		switch ($tag) {
			
			/*
			 * @param is supposed to have the @param, followed by a type, the
			 * name  of the parameter followed by its description
			 */
			case '@param'	:
			
				foreach ($data as $i => $o) {
					(array) $t = array ();
					
					$t = preg_split("/[\s,]+/", $o);

					$result[ $id ][$i]['key'] = $t[0];
					$result[ $id ][$i]['type'] = $t[1];
					$result[ $id ][$i]['parameter'] = $t[2];
					
					unset($t[0]);
					unset($t[1]);
					unset($t[2]);
					$result[ $id ][$i]['description'] = implode(' ', $t);
				}
				
				break;
			
			/*
			 * @returns will have the key @return with the type of data followed
			 * by a comment
			 */
			case '@return'	:
			
				foreach ($data as $i => $o) {
					(array) $t = array ();
					
					$t = preg_split("/[\s,]+/", $o);

					$result[ $id ][$i]['key'] = $t[0];
					$result[ $id ][$i]['type'] = $t[1];
					
					unset($t[0]);
					unset($t[1]);
					$result[ $id ][$i]['description'] = implode(' ', $t);
				}
				
				break;
			
			case '@var'	:
			
				$y = explode(' ', $data[0]);
				#$result[ $id ]['key'] = 
			
				$result[ $id ][0]['key'] = $tag;
				$result[ $id ][0]['type'] = $y[1];
			

				break;
			
			case '@see'	:
			
				foreach ($data as $i => $o) {
					(array) $t = array ();
					
					$t = preg_split("/[\s,]+/", $o);

					$result[ $id ][$i]['key'] = $t[0];
					
			
					
					if (strlen($t[2]) > 1) {
						$result[ $id ][$i]['title'] = $t[2];
						unset($t[2]);
					}
					unset($t[0]);
	
					$result[ $id ][$i]['link'] = implode(' ', $t);
					
					if (!isset($result[ $id ][$i]['title'])) {
						$h = explode('/', $result[ $id ][$i]['link']);
						$result[ $id ][$i]['title'] = trim(ucfirst($h[ count($h)-1 ]));
					}
				}
				
				break;
			
			case '@author'	:
			
				$y = explode(' ', $data[0]);			
				$result[ $id ]['key'] = $tag;
				unset($y[0]);
				
				$y = implode(' ', $y);
				$y = explode('<', $y);
				$result[ $id ]['author'] = $y[0];
				
				$y = explode('>', $y[1]);
				$result[ $id ]['mail'] = $y[0];

				break;
			
			case '@package'	:
			
				$y = explode(' ', $data[0]);			
				$result[ $id ]['key'] = $tag;
				$result[ $id ]['package'] = $y[1];			

				break;
			
			case '@version'	:
			
				$y = explode(' ', $data[0]);			
				$result[ $id ]['key'] = $tag;
				$result[ $id ]['version'] = $y[1];			

				break;
			/*
			 * in the case of blank tags we don't do any special parsing, just
			 * dump the array in to the main array'
			 */
			case ' '	:
			
				$result = $matches;

				break;
			
			default			:
				$result = $matches;
		};
		
	}
	

	/*
	 * if not @ tag was looked for, we assume we are returning just comments
	 * (the intro and description). So we combine multi line comments to a
	 * single line and return them with the 'intro' and 'description' keys
	 */
	if ($tag[0] != '@') {
		
		foreach ($result[1] as $id =>$data) {
			
			if ($data == '*') {
				$i++;
			} else {	
				
				$pp[$i] .= ltrim($data, '*');
			}
		}
		
		$result = array ();
		
		$result[0]['intro'] = $pp[0];

		/*
		 * make sure that the description actually exists, and it isn't a @tag
		 */
		$yy = trim($pp[1]);
		if ($yy[0] != '@') {
			$result[0]['description'] = $pp[1];
		}
		

	

	}
	
	if (isset($result[1])) {
		unset($result[1]);
		$result = $result[0];
	}

	return $result;
}
/**
 * Displays documentation and an example for the URI specified
 * 
 * Takes two $_GET parameters 'f' and 's'.  The folder and the file are used to
 * grab the .htm and .php files from the manual/example directory and displays
 * them.
 */
public function index ()
{	
	(array) $array = array ();
	(string) $key = '';
	(string) $value = ucfirst($value);
	(array) $methodDetailData = array ();
	
	if (isset($_GET['s'])) {
		$this->display->stylesheet = 'detailapi';
	} else {
		die ();
	}

	$data = explode('.', $_GET['s']);
	$data = $data[0];

	if (isset($_GET['site'])) {
		require_once 'site/'.$_GET['site'].'/controller/application_Controller.php';
		require_once 'site/'.$_GET['site'].'/controller/'.$data.'.php';

		$temp = $data;
		if (strpos($data, '_Controller') != 11) {
			$data .= '_Controller';
		}
	}

	$class = new ReflectionClass($data);
		
	$properties = $class->getProperties();
	$methods = $class->getMethods();

	
	
	$classComment = $class->getDocComment();
	
	$docBlock['controller'] = ucfirst($temp);
	$docBlock['description'] = $this->getDocComment($classComment, ' ');
	$docBlock['author'] = $this->getDocComment($classComment, '@author');
	$docBlock['see'] = $this->getDocComment($classComment, '@see');
	$docBlock['version'] = $this->getDocComment($classComment, '@version');
	$docBlock['package'] = $this->getDocComment($classComment, '@package');

	$this->display->addData($docBlock, 'class');
	$this->display->addData($this->getProperty($properties), 'properties');
	
	$methods = $this->getMethod($methods);
	$this->display->addData($methods, 'methods');

	foreach ($methods as $key => $value) {
		$methodDetailData[] = $this->getMethodDetail($data, $value['name']);
	}
	
	$this->display->addData($methodDetailData, 'methoddetail');
	
	$this->title = 'Controller - ' . $docBlock['controller'];

	
}


private function getProperty ($data)
{
	/*
	 * loops through all the properties, passing the class and property name to
	 * the reflection class.
	 */
	foreach ($data as $key => $value) {

		$propertyClass = new ReflectionProperty($value->{'class'}, $value->name);
		
		/**
		 * we check to see if this property belongs to this class, or the parent
		 * classes. Don't return details for properties that belong to parent
		 * classes
		 */
		$class = $propertyClass->getDeclaringClass();
		
		if ($class->{'name'} == $value->{'class'}) {
			$str = $propertyClass->getDocComment();
	
			$type = $this->getDocComment($str, '@var');
	
			$intro = $this->getDocComment($str, ' ');
	
			$subArray = array ();
			
			if ($propertyClass->isPublic()) {
				$subArray['visibility'] = '<img alt="Public" src="shared/ivy/images/icons/class_public.png" />';
			}
			if ($propertyClass->isPrivate()) {
				$subArray['visibility'] = '<img alt="Private" src="shared/ivy/images/icons/class_private.png" />';
			}
			if ($propertyClass->isProtected()) {
				$subArray['visibility'] = '<img alt="Protected" src="shared/ivy/images/icons/class_protected.png" />';
			}
			$subArray['name'] = $value->name;		
			$subArray['intro'] = $intro[0]['intro'];
			$subArray['type'] = $type[0]['type'];
	
	
			$array[] = $subArray;
		}
	}
	return $array;
}

private function getMethod ($data)
{
	/*
	 * loops through all the methods, passing the class and method name to the reflection 
	 * class.
	 */
	foreach ($data as $key => $value) {

		$methodClass = new ReflectionMethod($value->{'class'}, $value->name);
		
		
		/**
		 * we check to see if this method belongs to this class, or the parent
		 * classes. Don't return details for methods that belong to parent
		 * classes
		 */
		$class = $methodClass->getDeclaringClass();
		
		if ($class->{'name'} == $value->{'class'}) {
			$str = $methodClass->getDocComment();
	
			$intro = $this->getDocComment($str, ' ');
	
			$subArray = array ();
			
			if ($methodClass->isPublic()) {
				$subArray['visibility'] = '<img alt="Public" src="shared/ivy/images/icons/class_public.png" />';
			}
			if ($methodClass->isPrivate()) {
				$subArray['visibility'] = '<img alt="Private" src="shared/ivy/images/icons/class_private.png" />';
			}
			if ($methodClass->isProtected()) {
				$subArray['visibility'] = '<img alt="Protected" src="shared/ivy/images/icons/class_protected.png" />';
			}
			$subArray['name'] = $value->name;		
			$array[] = $subArray;
		}
	}
	return $array;
}

private function getMethodDetail ($class, $method)
{
	(array) $array = array ();
	(object) $methodClass = new ReflectionMethod($class, $method);
	
	$str = $methodClass->getDocComment();

	$text = $this->getDocComment($str, ' ');


	$array['intro'] = $text[0]['intro'];
	$array['description'] = $text[0]['description'];
	$array['return'] = $this->getDocComment($str, '@return');
	$array['class'] = $class;
	$array['filename'] = $methodClass->getFileName();
	$array['line'] = $methodClass->getStartLine();
	$array['manuallinks'] = $parameters = $this->getDocComment($str, '@see');
	
	$parameters = $this->getDocComment($str, '@param');
	
	#print_r($methodClass->getParameters());

	foreach ($parameters as $id => $value) {
		$array['parameters'][$id] = $value;
	}
	
	
	$start = $methodClass->getStartLine() - 2;
	$end = $methodClass->getEndLine();

	$file = file($methodClass->getFileName());
	
	foreach ($file as $id => $value) {
		if ($id <= $start || $id >= $end) {
			unset($file[$id]);
		}
	}
	
	$array['file'] = highlight_string("<?php \n " . wordwrap(implode(' ', $file), 110) . ' ?>', true);
	
	if ($methodClass->isPublic()) {
		$array['visibility'] = 'public';
	}
	if ($methodClass->isPrivate()) {
		$array['visibility'] = 'private';
	}
	if ($methodClass->isProtected()) {
		$array['visibility'] = 'protected';
	}
	
	$array['name'] = $methodClass->name;

	return $array;
}




/**
 * stuff goes here
 * @comment this is a big comment that spans
 * multiple lines
 * 
 * @id 3
 */
public function status ()
{
	(array) $fileArray = Ivy_File::selectFile('core');
	
	$this->stylesheet = 'indexmanual';
	
	$curl = new Ivy_Curl();
	$data = $curl->select('http://ivy.svn.sourceforge.net/viewvc/ivy/trunk/core/');
	
	$html = new Ivy_Html();
	$html->html = $data;
	
	foreach ($fileArray as $key => $data) {
		
		$data = explode('.', $data);
		$data = $data[0];
		$class = new ReflectionClass($data);
		
//print_r($this->parseComment($class->getDocComment()));
		$comment = $class->getDocComment();
		$commentArray = $this->parseComment($comment);
		
		
		$array[ $key ]['package'] = $commentArray['package'];
		$array[ $key ]['title'] = $data;
		$array[ $key ]['version'] = $commentArray['version'];
		$array[ $key ]['summary'] = $commentArray['package'];
		$arr = $html->select("//td/a[contains(@href, '" . $key . "')]");
		$array[ $key ]['last'] = $arr[0]['data'];
	}	
	
	#$this->display->addData($array);
	
	
	
	
// Print class properties
//printf("---> Properties: %s\n", var_export($class->getProperties(), 1));

// Print class methods
//printf("---> Methods: %s\n", var_export($class->getMethods(), 1));	
}


private function parseComment ($comment)
{
	(array) $array = array ();
	(string) $key = '';
	(string) $value = ucfirst($value);
	
	$doc = ltrim( rtrim($comment, '*/'), '/**');
	$doc = explode('@', $doc);
	
	foreach ($doc as $data) {
		$data = trim($data);
		$key = substr($data, 0, strpos($data, "\t")); 
		$data = str_replace('*', '', $data);
		$value = ltrim($data, $key); 
		
		switch ($key) {
			case '*'			:
				$key = 'DESCRIPTION';
				break;
			
			case 'comment'	:
				$key = 'COMMENT';
				break;
			
			default			:
		};
		
		$array[$key] = $value;
	}
	return $array;
}

}
?>