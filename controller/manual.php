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
 
class manual_Controller extends admin {

protected $title = 'Manual';
private $manualPath = 'core/manual';

public function _base () 
{
	$primaryFolders = Ivy_File::selectFolder($this->manualPath);
	
	foreach ($primaryFolders as $key => $value) {
		if ($value == '.svn') { continue; }
		$nav[ $value ]['title'] = ucfirst($value);
		$nav[ $value ]['menu'] = 'context';
		$nav[ $value ]['query'] = 's=' . $value;
		
	}
	return $nav;
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
	(string) $dir = "$this->manualPath/" . $_GET['s'];
	(string) $temp = '';
	(string) $file = '';
	(array) $breadcrumb = array (
		'<a href="index.php?controller=manual">Home</a>');
	
	$_GET['s'] = (isset($_GET['s']) ? $_GET['s'] : null);
	
	$this->stylesheet = 'detailmanual';

	$pathParts = explode('/', $_GET['s']);
	
	$file = $pathParts[ count($pathParts) -1 ];
	$folder = $_GET['s'];

	$folderList = Ivy_File::selectFolder("$this->manualPath/" . $pathParts[0]);

	rsort($pathParts);
	foreach ($pathParts as $id => $value) {
		$temp = explode($value.'/', urldecode($_GET['s']));
		$breadcrumb[] = "<a href='index.php?controller=manual&" .
			"s=$temp[0]'>" . ucfirst($value) . "</a>";
	}
	
	$breadcrumb = implode(' >> ', $breadcrumb);
	
	$this->display->addText(
		'<div class="breadcrumb">' . $breadcrumb . '</div>'
	);
	
	if (isset($_GET['s'])) {
		foreach ($folderList as $id => $value) {
			if ($value == '.svn') { continue; }
			$nav[$id] = array (
				'title'		=>	ucfirst($value),			
				'menu'		=>	'context',
				'action'	=>	'example',
				'query'		=>	's=' . $pathParts[0] . '/' . $value,
				'parent'	=>	$pathParts[0]
			);
		}
	}
	
	$this->addNavigation($nav);
	
	/*
	 * pull out a list of folders/file from the documentation folder
	 */
	$subFolderList = Ivy_File::selectFolder($dir);

	$htmContents = Ivy_File::select($dir.'/'.$file.'.htm');
	$phpContents = Ivy_File::select($dir.'/'.$file.'.php');

	if ($htmContents !== false) {
		$this->display->addText($htmContents);
	} else {
		$this->display->addText(Ivy_File::select($dir.'/example.htm'));
	}
	
	if ($phpContents !== false) {
		$this->display->addText(
			'<h4>Example code</h4>' . highlight_string($phpContents, true)
		);
	}
	if ($subFolderList !== false) {
		foreach ($subFolderList as $id => $value) {
			if ($value == '.svn') { continue; }
			$value = explode('.', $value);
			
			if ($value[1] == 'php') {
				continue;
			}
			$value = $value[0];
			
			/*
			 * build the links to the files
			 */
			$docFiles[] = "<li><a href='index.php?controller=manual&" .
				"s=$folder/$value'>" . ucfirst($value) . "</a></li>";
		}
	
		$this->display->addData($docFiles);
	}
	
	$this->display->setParameter('title', ucfirst($file));
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
	
	$this->display->addData($array);
	
	
	
	
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