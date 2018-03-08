<?php
/**
 * SVN FILE: $Id: Ivy_Template.php 19 2008-10-02 07:56:39Z shadowpaktu $
 *
 * Project Name : Project Description
 *
 * @package className
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 19 $
 * @lastrevision $Date: 2008-10-02 08:56:39 +0100 (Thu, 02 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-02 08:56:39 +0100 (Thu, 02 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Template.php $
 */

class Ivy_Borne {
	
var $counter;			// maintains a count of how many times a string has been replaced with php code
var $loopDepth = 0;		// has the depth of the loop
private $globalTemplate;	// contains the name of the global template used
private $localTemplate;		// contains the name of the local template used
private $globalPath = '';
private $localPath = '';
var $data;				// contains all the data required to generate output (data is suplies mostly by controller scripts
	
public static $instance;
private $config;
	
/**
 * Well fancy that! A new addition to the bourne class in years! (August 2017)
 * Tells the engine if we output the html to the screen or just return it
 *
 * @author				James Randell <jamesrandell@me.com>
 * @access				public
 * @var					bool
 * @datecreated			2017/08/18
 * @datemodified		2017/08/18
 */
public $output = true;


	public function __construct ($data, $settings) {
		$this->data = $data;

		if (isset($settings['localPath'])) {
			$this->localPath = $settings['localPath'];
		}
		if (isset($settings['globalPath'])) {
			$this->globalPath = $settings['globalPath'];
		}
		
		$registry = Ivy_Registry::getInstance();
		$this->config = $registry->selectSystem('config');
	}
	
	/*
	public static function getInstance ($data, $settings) {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c($data, $settings);
		}

		return self::$instance;
	
	}
	*/
	
	
	private function nameCache () {
		// takes the two class vars local/global template and concatinates them
		$tempvar1 = preg_replace('/[\W_]+/', '', $this->globalTemplate);
		$tempvar2 = preg_replace('/[\W_]+/', '', $this->localTemplate);
		
		return $tempvar1 . '-' . $tempvar2;
	}
	
	
	private function processInclude ($path) {
		// parses any included files and returns the result{
		$string = $this->getFile($path);
		$var = preg_replace_callback('/{(.*?)}/', array(&$this, 'match'), $string);
		return $var;

	}
	
	
	
	
	function test ($array) {
		if (is_array($array)) {
			$this->data = array_merge($this->data, $array);
		}
	}

	public function parse ($localTemplate, $globalTemplate = NULL) {	
		$registry = Ivy_Registry::getInstance();
		$config = $registry->selectSystem('config');
		
		$this->localTemplate = $localTemplate;
		
		if ($globalTemplate) {
			$this->globalTemplate = $globalTemplate;
		}
		
		/**
		 * if the app has done something funky, like use sub-folders for templates (could be a 'mobile' folder), then they would have put
		 * a slash in for the driectory structure. Just parse this out so we save the caches tempalte without it
		 */
		$globalTemplateCache = str_replace('/', '-slash-', $globalTemplate);
		$localTemplateCache = str_replace('/', '-slash-', $localTemplate);
		

		if (!file_exists(SITEPATH . '/site/' . SITE . '/resource/cache/template/' . $globalTemplateCache . '-'. $localTemplateCache . '.htm') || $config['output']['cache'] == 0) {

			$string = $this->getFile($this->globalPath . $globalTemplate . '.htm');

			$var = preg_replace_callback('/{(.*?)}/', array(&$this, 'match'), $string);
			
			/*
			 * remove wierd UTF-8 BOM charector that sometimes gets into templates from a code editor
			 */
			$var = str_replace("\xEF\xBB\xBF",'',$var);
			
			if (is_dir(SITEPATH . '/site/' . SITE)) {
				file_put_contents(SITEPATH . '/site/' . SITE . '/resource/cache/template/' . $globalTemplateCache . '-'. $localTemplateCache . '.htm', $var);
				return $this->build(SITEPATH . '/site/' . SITE . '/resource/cache/template/' . $globalTemplateCache . '-'. $localTemplateCache . '.htm');
			} else {
				file_put_contents( SITEPATH . '/shared/' . THEME . '/cache/template/' . $globalTemplateCache . '-'. $localTemplateCache . '.htm', $var);
				return $this->build( SITEPATH . '/shared/' . THEME . '/cache/template/' . $globalTemplateCache . '-'. $localTemplateCache . '.htm');
			}
		}
	}

private function build ($template) {

	/**
	 * array is the data array used by the included file
	 */
	$array = $this->data;
	
	if ($this->output === true) {
		error_reporting(E_ALL ^ E_NOTICE);
		include_once $template;
		error_reporting(E_ALL);
	} else {
		ob_start();
		include_once $template;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

}


	function match ($string) {
		$s = explode(' ', $string[1]);
		
		(string) $tempResult = ''; // temp var used to hold volatile information
		(string) $result = ''; // the result string
		(string) $tempPath = ''; // holds a temp path for widget or local template
		
		switch ($s[0]) {
			case 'loop'		:
				$localLoopDepth = $this->loopDepth;
				
				if ($this->loopDepth > 0) { // this loop is in another loop
					$temp = $this->loopDepth-1;					
				}
				
				if (isset($s[1])) {
					$array = explode('/', $s[1]);
					$str = '';
					foreach ($array as $key => $value) {
						if (defined(strtoupper($value))) {
							
						} else if ($value[0] != '$') {
							$value = "'$value'";
						}
						if ($value == '..') {
							if ($temp == 0) {
								$localLoopDepth = 0;
								$str .= "[$value]";
							} else {
								$temp = $temp -1;
							}
						} else {
							$str .= "[$value]";
						}
					}
				} else {
					$str = '';
				}
				if ($localLoopDepth > 0) { // this loop is in another loop
					$result = '<?php if (isset($value' . $temp . $str . ')) { foreach ($value' . $temp . $str . ' as $key' . $this->loopDepth . ' => $value' . $this->loopDepth . ') { ?>';
				} else {
					$result = '<?php if (isset($array' . $str . ')) { foreach ($array' . $str . ' as $key' . $this->loopDepth . ' => $value' . $this->loopDepth . ') { ?>';
				}


				$this->loopDepth++;
			break;
			case 'value'	:
				$temp = $this->loopDepth-1;
				if ($temp < 0) {
					$temp = 0;
				}
				
				if (isset($s[1])) {
					$array = explode('/', $s[1]);
					if ($array[0] == '/') {
						$temp = $temp+1;
					}
					
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
				
					$result = '<?php if (isset($value' . $temp . $str . ')) {echo $value' . $temp . $str . ';} ?>';
				} else {
					$result = '<?php if (isset($value' . $temp . ')) {if (is_array($value' . $temp . ')) {print_r($value' . $temp . ');} else {echo $value' . $temp . ';}} ?>';
				}
				
			break;
			case 'valuesafe'	:
				$temp = $this->loopDepth-1;
				if ($temp < 0) {
					$temp = 0;
				}
				
				if (isset($s[1])) {
					$array = explode('/', $s[1]);
					if ($array[0] == '/') {
						$temp = $temp+1;
					}
					
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}

					$result = '<?php if (isset($value' . $temp . $str . ')) {echo str_replace(\'/\', \'\', $value' . $temp . $str . ');} ?>';
				} else {
					$result = '<?php if (isset($value' . $temp . ')) {if (is_array($value' . $temp . ')) {print_r($value' . $temp . ');} else {echo str_replace(\'/\', \'\', $value' . $temp . ');}} ?>';
				}
				
			break;
			case 'if'	:
				$temp = $this->loopDepth-1;
				
				$arrayKey = '$value';
				if ($temp < 0) {
					$arrayKey = '$array';
					$temp = '';
				}
				
				if ($s[1] == 'value') {
					$array = explode('/', $s[2]);
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
					
					
					
				
					if (!isset($s[3])) {
						$result = '<?php if (isset(' . $arrayKey . $temp . $str . ')) { ?>';
						
					} else {
						if ($s[4][0] != "'") {
							$s[4] = '$' . $s[4];
						}
						$result = '<?php if (' . $arrayKey . $temp . $str . ' == ' . $s[4] . ') { ?>';
					}
				} else if ($s[1] == 'key') {
					if ($s[3][0] != "'") {
						$s[3] = '$' . $s[3];
					}
					$result = '<?php if ($key' . $temp . ' == ' . $s[3] . ') { ?>';				
				} else {
					$array = explode('/', $s[1]);
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
					if ($s[3][0] != "'") {
						$s[3] = '$' . $s[3];
					} else {
						$uu = count ($s);
						
						for ($i = 4; $i <= count($s); $i++) {
							$s[3] .= ' ' . $s[$i];
						}
					}
					$result = '<?php if (' . $arrayKey . $temp . $str . ' == ' . $s[3] . ') { ?>';
				}
				

			break;
			case 'elseif'		:
				$temp = $this->loopDepth-1;
				if ($temp < 0) {
					$temp = 0;
				}
				if ($s[1] == 'value') {
					$array = explode('/', $s[2]);
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
					if ($s[4][0] != "'") {
						$s[4] = '$' . $s[4];
					}
					$result = '<?php } else if ($value' . $temp . $str . ' == ' . $s[4] . ') { ?>';
				} else {
					$array = explode('/', $s[1]);
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
					if ($s[3][0] != "'") {
						$s[3] = '$' . $s[3];
					} else {
						$uu = count ($s);
						
						for ($i = 4; $i <= count($s); $i++) {
							$s[3] .= ' ' . $s[$i];
						}
					}
					$result = '<?php } else if ($value' . $temp . $str . ' == ' . $s[3] . ') { ?>';
				}
			break;
			case 'else'			:
				$result = '<?php } else { ?>';
			break;
			case 'constant'		:
				$result = '<?php $' . $s[1] . ' = ';
				
				$temp = $this->loopDepth-1;
				if ($temp < 0) {
					$temp = 0;
				}
				
				if ($s[3] == 'value') {
					$array = explode('/', $s[4]);
					$str = '';
					foreach ($array as $key => $value) {
						$str .= "['$value']";
					}
				
					$result .= '$value' . $temp . $str . '; ?>';
				} else {
					$result .= '$value' . $temp . '; ?>';
				}
				
			break;
			case 'widget'		:				
				$widgetName = $s[1];
				
				if (is_readable(SITEPATH . '/shared/' . THEME . '/view/widget/' . $widgetName)) {

					$tempPath = SITEPATH . '/shared/' . THEME . '/view/widget/' . $widgetName;	

				} else if (is_readable(SITEPATH . '/site/' . SITE . '/view/widget/' . $widgetName)) {
					
					$tempPath = SITEPATH . '/site/' . SITE . '/view/widget/' . $widgetName;
			
				}
				
				if (isset($s[2])) {
					$string = $this->getFile($tempPath);
					$patterns[0] = "/form\/default/";
					$patterns[1] = "/\['form'\]\['default'\]/";
					$patterns[2] = "/result\/default/";
					$patterns[3] = "/\['result'\]\['default'\]/";
					$replacements[0] = "form/$s[2]";
					$replacements[1] = "['form']['$s[2]']";
					$replacements[2] = "result/$s[2]";
					$replacements[3] = "['result']['$s[2]']";
					$string = preg_replace($patterns, $replacements, $string);
					#print_r($string);
					$tempResult = preg_replace_callback('/{(.*?)}/', array(&$this, 'match'), $string);

				} else {
					$tempResult = $this->processInclude($tempPath);
				}
				
				
				if ($this->config['output']['debug'] == 1) {
					$result = '<div>' .
							'<div style="clear:both;float:left;color:#000;padding:5px;margin:2px;border:1px solid #999;background:#fff;"><strong>widget: </strong><a href="' . $tempPath . '">' . $widgetName . '</a></div>' . $tempResult . '</div>';
							$result = $tempResult;
				} else {
					$result = $tempResult;
				}
		
			break;
			case 'block'		:				
				
				(string) $tempString = '';
				#(array) $result = array ();
				
				if (isset($this->data['system']['block'])) {
					
				
					foreach ($this->data['system']['block'][ $s[1] ] as $id => $widget) {
						$widget = $widget . '.htm';
						
						if (is_readable(SITEPATH . '/site/' . SITE . '/resource/template/widget/' . $widget)) {					
							$tempPath = SITEPATH . '/site/' . SITE . '/resource/template/widget/' . $widget;					
						} else if (is_readable(SITEPATH . '/site/' . SITE . '/view/widget/' . $widget)) {						
								   $tempPath = SITEPATH . '/site/' . SITE . '/view/widget/' . $widget;					
						} else if (is_readable(SITEPATH . '/extension/' . EXTENSION . '/view/widget/' . $widget)) {						
								   $tempPath = SITEPATH . '/extension/' . EXTENSION . '/view/widget/' . $widget;					
						} else if (is_readable(SITEPATH . '/shared/' . THEME . '/template/widget/' . $widget)) {						
							$tempPath = SITEPATH . '/shared/' . THEME . '/template/widget/' . $widget;					
						} else {						
							$tempPath = SITEPATH . '/shared/' . THEME . '/template/widget/' . $widget;						
						}
					}
					$result = $this->processInclude($tempPath);
				}

		
			break;
			case 'local'		:

				
				$tempResult = $this->processInclude($this->localPath . $this->localTemplate . '.htm');
				if ($this->config['output']['debug'] == 1) {
					$result = '<div>' .
							'<div style="clear:both;float:left;color:#000;padding:5px;margin:2px;border:1px solid #999;background:#fff;"><strong>local: </strong>' . $this->localTemplate . '</div>' . $tempResult . '</div>';
							$result = $tempResult;
				} else {
					$result = $tempResult;
				}
			
			break;
			
			case 'key'	:
				$temp = $this->loopDepth-1;
				$result = '<?php echo $key' . $temp . '; ?>';
				
			break;
			case 'endloop'		:
				$result = '<?php }} ?>';
				
				$this->loopDepth = $this->loopDepth-1;
			break;
			case 'endif'		:
				$result = '<?php } ?>';
				
			break;
			default			:
				$array = explode('/', $s[0]);
				$str = '';
				foreach ($array as $key => $value) {
					$str .= "['$value']";
				}
				
				$result = '<?php if (isset($array' . $str . ')) {echo $array' . $str . ';} ?>';
			
			break;
		}
		

		
		$this->counter++;
		return $result;
	}		
	
	
	
	function getFile ($name) {
		$var = file_get_contents($name);
		return $var;
	}
	
	function process ($content) {

	}
	

}

?>