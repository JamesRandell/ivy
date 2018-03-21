<?php
/**
 * Templating module for Ivy
 * 
 * Deals with managing display type data to be outputted to the user in some
 * format. Containing methods to create things like forms, results, detail
 * screens, navigation elements and more.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 2.0
 * @package Template
 * @updated	23rd April, 2012
 * 
 */
class Ivy_View {

/**
 * Contains all of the display data
 * 
 * @access private
 * @var array
 */
public $data = array ();

/**
 * Restricts the template to only display these fields
 * @access public
 * @var array
 */
public $fields = array ();
	
/**
 * The template engine to use, starts empty. is filled by the config value first
 * @access public
 * @var string
 */
public $engine = '';
	
/**
 * Single template instance
 * 
 * @access public
 * @var object
 */
public static $instance;
	
/**
 * Reference to the Registry object
 * 
 * @access private
 * @var object
 */
private $registry;
	
/**
 * Local stylesheet declaration
 * 
 * @access	public
 * @var		string
 */
public $stylesheet = 'default';

/**
 * Global stylesheet declaration
 * 
 * @access	public
 * @var		string
 */
public $globalstylesheet = 'default';

/**
 * sets a header to tell the browser what content to expect
 * can be overridden when called from a controller
 * 
 * @access	public
 * @var		string
 */
public $contentType = 'html';

/**
 * 19/12/2018
 * James Randell
 *
 * Sets which extension calls the view. Extensions may invoke the same instane of the view class so they can interract
 * with the applications view or replace it entirley
 */
public $extension = '';


private $encrypt_field_name = true;

private $form_field_encryption_key = '';

/**
 * Do we run it to the display or just store the output in a variable (for things like email)
 * 
 * @access	public
 * @var		bool
 */
public $output = true;


/**
 * Private constructer so we can't directly instantiate this class
 * 
 * @access	private
 */	
public function __construct () {
	$this->registry = Ivy_Registry::getInstance();
	$this->error = Ivy_Error::getInstance();
	$this->form_field_encryption_key = $var['form_field_encryption_key'] = time();
	$this->registry->insertSession($var);
}

/**
 * Method to generate only a single instance of this class ever
 * 
 * @access	public
 */
public static function getInstance () {
	if (!isset(self::$instance)) {
		self::$instance = new Ivy_View();
	}
	
	//self::$instance->registry = Ivy_Registry::getInstance();
	//self::$instance->error = Ivy_Error::getInstance();
	//self::$instance->form_field_encryption_key = $var['form_field_encryption_key'] = time();
	//self::$instance->registry->insertSession($var);
	
	return self::$instance;	
}

/**
 * alias of addParamter
 * 
 * @access	public
 * @param	string	$key	Name of the key to access the value by
 * @param	mixed	$value	The keys value
 * @return	void
 */
public function setParameter ($key, $value) {
	$this->addParameter($key, $value);
}

public function set ($key, $value)
{
	$this->addParameter($key, $value);
}
public function insert ($key, $value) {
	$this->addParameter($key, $value);
}

/**
 * Creates a single key in the data array
 * 
 * Handy method that can be used for creating keys to be read by a template
 * engine
 * 
 * @access	public
 * @param	string	$key	Name of the key to access the value by
 * @param	mixed	$value	The keys value
 * @return	void
 */
public function addParameter ($key, $value) {
	$this->data[$key] = $value;
}
	
	/**
	 * Add specific values to the display array on the first request.
	 * This method is called when getInstance () is called.
	 *
	 * @return	void
	 */
	private function buildParameters () {

		$this->header('contentType',$this->contentType);


		$tempPath = explode("/", $_SERVER['PHP_SELF']);
		unset($tempPath[ count($tempPath)-1 ]);
		$tempPath = implode("/", $tempPath);
	
		$this->data['system']['stat']['client'] = $this->registry->selectSession(0);

		if (!isset($this->data['system']['stat']['client']['language'])) {
			$this->data['system']['stat']['client']['language'] = 'default';
		}


		$config = $this->registry->selectSystem('config');
		$this->data['system']['var']['imagepath'] = 'shared/images/';

		if ($_SERVER['HTTPS'] != 'on') {
			$this->data['sharedpath'] = 'http://' . $_SERVER['SERVER_NAME'] . $tempPath . '/shared/' . THEME . '/';
			$this->data['sharedpath'] = $tempPath . '/shared/' . THEME . '/resource/';
		} else {
			$this->data['sharedpath'] = 'https://' . $_SERVER['SERVER_NAME'] . $tempPath . '/shared/' . THEME . '/';
			$this->data['sharedpath'] = $tempPath . '/shared/' . THEME . '/resource/';
		}	
		if (defined('EXTENSION')) {
			$this->data['extensionpath'] = 'extension/' . EXTENSION . '/resource/';
		}
		$this->data['script'] = $_SERVER['PHP_SELF'];
		#$this->data['title'] = $this->data['actiontitle'];

		if ($_SERVER['HTTPS'] != 'on') {
			$this->data['resourcepath'] = 'http://' . $_SERVER['SERVER_NAME'] . $tempPath . '/site/' . SITE . '/resource/';
			$this->data['resourcepath'] = $tempPath . '/site/' . SITE . '/resource/';
		} else {
			$this->data['resourcepath'] = 'https://' . $_SERVER['SERVER_NAME'] . '/' . SITE . '/site/' . SITE . '/resource/';
			$this->data['resourcepath'] = SITE . '/site/' . SITE . '/resource/';
		}
		
		$this->data['system']['var']['sitepath'] = SITEPATH .'/site/' . SITE;
		
		if ($_SERVER['HTTPS'] != 'on') {
			$this->data['sitepath'] = 'http://' . $_SERVER['SERVER_NAME'] . $tempPath . $_SERVER['PHP_SELF'];
			$this->data['sitepath'] = $tempPath . $_SERVER['PHP_SELF'];
		} else {
			$this->data['sitepath'] = 'https://' . $_SERVER['SERVER_NAME'] . $tempPath . $_SERVER['PHP_SELF'];
			$this->data['sitepath'] = $tempPath . $_SERVER['PHP_SELF'];
		}	

		$this->data['query'] = $_SERVER['QUERY_STRING'];
		
		$this->data['system']['info']['longname'] = $config['system']['name'];
		$this->data['system']['info']['palette']['primary'] = $config['system']['color'];
		$this->data['system']['var']['scripttime'] = timerSelect();
		
		foreach ($_GET as $key => $value) {
			$this->data['get'][ $key ] = $value;
		}
		
		$get = $this->registry->selectSystem('get');

		(string) $string = '';

		if ($get['controller'] != 'index') {
			$string .= '<a href="index.php">Home</a>';
		}
		
		$this->data['controllertitle'] = (isset($this->data['controllertitle']) ? $this->data['controllertitle'] : $get['controller']);
		//$string .= ' >> <a href="index.php?controller=' . $get['controller'] . '">' . $this->data['controllertitle'] . '</a>';
		
		//if ($get['action'] != 'index') {
		//	$string .= ' >> <a href="index.php?controller=' . $get['controller'] . '&action=' . $get['action'] . '">' . $this->data['actiontitle'] . '</a>';
		//}
		

		//$this->data['breadcrumb'] = (isset($this->data['breadcrumb'])) ? $this->data['breadcrumb'] : $string;

		if (isset($_SERVER['REQUEST_TIME'])) { // time this page was requested
			$this->data['system']['stat']['server']['generated'] = date('M jS, H:i', $_SERVER['REQUEST_TIME']);
		}
		
		if (isset($_SERVER['PHP_SELF'])) {// any $_GET vars on the URL
			$this->data['system']['stat']['server']['pathPage'] = htmlspecialchars($_SERVER['PHP_SELF']);
			$this->data['system']['var']['page'] = $_SERVER['PHP_SELF'];
		}
		
		if (isset($_SERVER['QUERY_STRING'])) {// any $_GET vars on the URL
			/**
			 * new cleaned variable to set the URL without the guff such as PAGE and UPDATE msgs
			 *
			 * @author 			James Randell <james.randell@curtisfitchglobal.com>
			 * @datemodified 	06/04/2017
			 */
			$this->data['system']['var']['page_clean'] = $this->data['system']['var']['page'] . '?' . $get['controller'] . '/' . $get['action'];
			foreach ($_GET as $key => $value) {
				if (is_numeric($key)) {
					$this->data['system']['var']['page_clean'] .= '/' . $value;
				}
			}


			$this->data['system']['stat']['client']['query'] = htmlspecialchars($_SERVER['QUERY_STRING']);
			$this->data['system']['var']['page'] .= '?' . $_SERVER['QUERY_STRING'];
			
			$this->data['script'] .= '?' . $_SERVER['QUERY_STRING'];

			
		}
		
		if (isset($_SERVER['HTTP_REFERER'])) { // id number for the user
			$this->data['system']['stat']['client']['referer'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
		}
		
		if (isset($_SERVER['REMOTE_USER'])) { // full domain/id string
			$this->data['system']['stat']['client']['user'] = $_SERVER['REMOTE_USER'];
		}
		
		if (isset($_SESSION['user_firstname'], $_SESSION['user_lastname'])) { // full name of user
			$this->data['system']['stat']['client']['username'] = $_SESSION['user_firstname'].' '.$_SESSION['user_lastname'];
		}
		
		if (isset($_SESSION['user_loggedin'])) { // is the user logged in
			$this->data['system']['stat']['client']['loggedin'] = 'yes';
		} else {
			$this->data['system']['stat']['client']['loggedin'] = 'no';
		}
		
		if (isset($_SERVER['SERVER_NAME'])) { // the server name or ip
			$this->data['system']['stat']['server']['host'] = $_SERVER['SERVER_NAME'];
		}
		
		if (isset($_SESSION['system_auth_strength'])) { // the server name or ip
			$this->data['system']['stat']['server']['auth_strength'] = $_SESSION['system_auth_strength'];
		}

		if (isset($_SESSION['user_loggedintime'])) { // the server name or ip
			$this->data['system']['stat']['client']['loggedintime'] = date($config['unixformat'], $_SESSION['user_loggedintime']);
		}
	}
	
	
	/**
	 * Add special rules to the contruction of page elements (such as results)
	 *
	 * For example you may only want to have one template to control the way results are built, but 
	 * if you have results that link to detail screens - the links may be different depending on what 
	 * controller is running the query.
	 *
	 * @param	$name			string			Name of the node to apply this rule too
	 * @param	$array			array			An array of rules as processed by the template
	 * @return	void
	 */
	public function special ($name, $array = array ()) {
		if (isset($this->data['result'][$name]['special'])) {
			$this->data['result'][$name]['special'] = Ivy_Array::merge($this->data['result'][$name]['special'], $array);
		} else {
			$this->data['result'][$name]['special'] = $array;
		}
		
		
		if (isset($array['controller'])) {
			$this->data['result'][$name]['controller'] = $array['controller'];
		}
		
		if (isset($array['action'])) {
			$this->data['result'][$name]['action'] = $array['action'];
		}
		
		if (isset($array['s'])) {
			$this->data['result'][$name]['s'] = $array['s'];
		}
		
	}
	
	/**
	 * Really simple method to add a value to the 'content' part of the display array
	 *
	 * @param	$text			string			The text to be added
	 * @param	$array			array			An array of rules as processed by the template
	 * @return	void
	 */
	public function addText ($text, $array = array ()) {
		(array) $data = array ();
		
		if (isset($array)) {
			$data['meta'] = $array;	
		}
		$data['data'] = $text;
		$this->data['content'][] = $data;	
	}
	
	/**
	 * Generates a tagcloud
	 *
	 * @param	$tagCloudArray	array			Array of tags from the data result
	 * @param	$array			array			Array of 'keywords' => 'count'
	 * @return	void
	 */
	public function addTagCloud ($tagCloudArray, $array = array ()) {
		// check to see if the array is empty
		if (empty($tagCloudArray)) {
			return;
		}
		
		(array) $array;
		(string) $string = '';
		(float) $maxFont = 1.5;
		(float) $cutOffFont = (isset($array['cutofflink']) ? $array['cutofflink'] : 1.4);
		(int) $minFont = 1;
		(int) $fontRange = $maxFont - $minFont;
		(int) $maxQty = max(array_values($tagCloudArray));
        (int) $minQty = min(array_values($tagCloudArray));
		(int) $size = 0;
		(int) $spread = $maxQty - $minQty;
		
		$array['id'] = (isset($array['id']) ? $array['id'] : 'tagcloud');
		
		$config = $this->registry->selectSystem('other');
		$array['action'] = (isset($array['action']) ? $config['script'] . 'index.php&action=' . $array['action'] : 'index.php?action=index');
		
		
		
		if ($spread == 0) { // we don't want to divide by zero
			$spread = 1;
		}
		
		(float) $step = ($maxFont - $minFont) / ($spread);
		

		$fontRange = $maxFont - $minFont;
		$string = '<div id="tagcloud" rel="' . $array['id'] . '">';
		ksort($tagCloudArray);
		foreach ($tagCloudArray as $word => $count) {
			$size = $minFont + (($count - $minQty) * $step);
			
			$string .= '<span style="margin:0 1px;font-size:' . $size . 'em;">';
			if ($size > $cutOffFont) {
				$s = (isset($array['url']) ? $array['url'] : 's'); 
				$string .= ' <a href="' . $array['action'] . '&' . $s . '=' . $word . '">';
			}
			$string .= $word;
			
			if ($size > $cutOffFont) {
				$string .= '</a> ';
			}
			$string .= '</span> ';
		}
		$string .= '</div>';

		$data['data'] = $string;
		$this->data['content']['tagcloud'] = $data;			
	}


/**
 * Generates a form
 * 
 * Accepts an object that contains 'schema' and 'data' arrays. Will also accept
 * an ID of a registry resultset (depreciated, objects should be passed
 * instead).
 * 
 * @see	example/display/example basic
 * @access	public
 * @return	bool
 * @param 	object|int	$schema	The ID or object of the data to use
 * @param	string		$name	Names the entry in the template data array
 * @param	array		$fields	Will display these fields if any are supplied
 */
public function addForm ($schema, $name = 'default', $fields = array ()) {	
	(int) $i = 0;
	(array) $tempDataArray = array ();
	(array) $config = $this->registry->selectSystem('config');
	(string)$dateFormat = $config['system']['unixformat'];
	
	if (is_object($schema)) {
		$tempDataArray['fieldSpec'] = $schema->schema['fieldSpec'];
		$tempDataArray['tableSpec'] = $schema->schema['tableSpec'];
		$tempDataArray['data'] = $schema->schema['data'];
	} else {
		$tempDataArray = Ivy_Registry::getInstance()->selectData($schema);
	}

	/**
	 * What trickkery is this? We perform a little bit of parameter sniffing here to see if the calling script has passed in an array for the name.
	 * If it has then we assume it's a field list and update the correct parameters
	 */
	if (is_array($name)) {
		$fields = $name;
		$name = 'default';
	}

	if (!empty($fields)) {
		$dataArray['fieldSpec'] = array ();
		foreach ($fields as $field) {
			//$fields[strtolower($field)] = strtolower($field);
			//echo $field.'<br>';
			if (strpos($field, '.') === false) {
				$field = $fields[$field] = $schema->schema['tableSpec']['name'] . '.' . $field;
			}
			$fields[$field] = $field;

			$dataArray['fieldSpec'][$field] = $tempDataArray['fieldSpec'][$field];
			$dataArray['tableSpec'] = $tempDataArray['tableSpec'];
			$dataArray['data'] = $tempDataArray['data'];
			
			$fields[$field] = strtolower($field);
		}
	} else {
		$dataArray = $tempDataArray;
	}


	/**
	 * this function should be much faster than calling strtolower every loop
	 * we do this because form the MODEL the keys are unchanged and we use whatever case
	 * the user specifies. When it comes to the VIEW however we turn everything to LOWER
	 * CASE, but have to convert back when moving back to the view. Bit of a faff but provides 
	 * the most portability
	 */
	$dataArray['fieldSpec'] = array_change_key_case($dataArray['fieldSpec'], CASE_LOWER);
	$fields = array_change_key_case($fields, CASE_LOWER);
	
	/**
	 * change the case for the data too or it may not match up if you've used camelcase in the model file!
	 */
	foreach ($dataArray['data'] AS &$row) {
		$row = array_change_key_case($row, CASE_LOWER);
	}
	foreach ($dataArray['fieldSpec'] as $metaField => $metaArray) {	

		$metaField_original = $metaField;
		if ($this->encrypt_field_name === true) {
			$options = array(
				'cost'	=>	10
			);

			$metaField = $this->encrypt_field_name($metaField);
		}

		if ($errorArray !== FALSE) {
			foreach ($errorArray as $key => $data) {
				$this->data['form'][$name][$metaField]['error'] = $data;
			}
		}

		if (!isset($metaArray['front']['nobuild'])) {
			
			if (!empty($fields) && !in_array($metaField_original, $fields)) {
				continue;
			}

			foreach ($metaArray['front'] as $key => $value) {
				$this->data['form'][$name][$metaField]['meta'][$key] = $value;
			}

			if ($metaField[0] == '_') {
				$this->data['form'][$name][$metaField]['meta']['type'] = 'hidden';
			}

			if (!isset($dataArray['data'][0][$metaField_original])) {
				continue;
			}

			switch ($metaArray['front']['type']) {
				case 'radio'	:
					if (isset($dataArray['data'][0]['_'.$metaField])) {
						$finalValue = $dataArray['data'][0]['_'.$metaField_original];
					} else {
						$finalValue = $dataArray['data'][0][$metaField_original];
					}
					$finalValue = trim($finalValue);
					break;
				case 'select'	:
					$finalValue = (isset($dataArray['data'][0]['_'.$metaField_original]) ? $dataArray['data'][0]['_'.$metaField_original] : $dataArray['data'][0][$metaField_original]);
					$finalValue = trim($finalValue);
					
					break;
				case 'checkbox'	:

					(string) $tempVar = '';
					$tempVar = (isset($dataArray['data'][0]['_'.$metaField_original]) ? $dataArray['data'][0]['_'.$metaField_original] : $dataArray['data'][0][$metaField_original]);
					if (is_string($tempVar)) {
						$tempVar = explode(',', $tempVar);						
					}
					
					$finalValue = array_flip($tempVar);

					$finalValue = (is_null($finalValue)) ? 1 : $finalValue;
					break;
				default			:
					$finalValue = (isset($dataArray['data'][0]['_'.$metaField_original]) ? $dataArray['data'][0]['_'.$metaField_original] : $dataArray['data'][0][$metaField_original]);
					// do a check to see if the value is that of a date object
					if (is_a($finalValue, 'DateTime')) {
						$finalValue = $finalValue->format('m/d/y H:i');
					} else {
						$finalValue = trim($finalValue);
					}
			}


			/**
			 * There are two replace commands in a MODEL file. The first runs a sub-query to get the data to replace the 
			 * original value, which is quite inefficiante but it does the job.
			 * The second way is this way, in which if you have a JOIN specified, you can take part or all of those fields 
			 * and replace the original value with them
			 */
			if (isset($metaArray['join']['replace'])) {
				$finalValue = trim($dataArray['data'][0][$metaField_original]);

				$metaFieldN = $this->encrypt_field_name('_'.$metaField_original);

				$this->data['form'][$name][$metaFieldN] = $this->data['form'][$name][$metaField];
				$this->data['form'][$name][$metaFieldN]['data']['value'] = $finalValue;
				
				

				$this->data['form'][$name][$metaField]['meta']['type'] = 'hidden';
				$this->data['form'][$name][$metaField]['meta']['title'] = $metaField;
				$this->data['form'][$name][$metaFieldN]['meta']['data']['replacedInputName'] = $metaFieldN;

				$metaArray['front']['value'] = trim($dataArray['data'][0]['_'.$metaField_original]);


				// unset any data attributes incase they conflict with the original
				//unset($this->data['form'][$name][$metaFieldN]['meta']['data']); 



			}


			if (isset($metaArray['front']['value'])) {
				$finalValue = $metaArray['front']['value'];
			}

			$this->data['form'][$name][$metaField]['data']['value'] = $finalValue;

			if ($metaArray['back']['type'] == 'unix') {
				if (isset($dataArray['data'][0][$metaField_original])) {
					if (strlen($dataArray['data'][0][$metaField_original]) == 10 && is_numeric($dataArray['data'][0][$metaField_original])) {
						$this->data['form'][$name][$metaField]['data']['value'] = date($config['system']['unixformat'], $dataArray['data'][0][$metaField]);
					} else {
						$this->data['form'][$name][$metaField]['data']['value'] = $dataArray['data'][0][$metaField_original];
					}
						
					if ($metaArray['front']['type'] == 'hidden') {
						$this->data['form'][$name][$metaField]['data']['value'] = $dataArray['data'][0][$metaField_original];
					}
				}
			}				
		}

		if (isset($metaArray['replace'])) {
			foreach ($metaArray['replace']['fields'] AS $replace_field) {
				$this->data['form'][$name][$metaField]['data']['value'] .= $dataArray['data'][0][$replace_field];
			}	
		}
	}
}

/**
 * Generates a formfield
 * 

 * 
 * @see	example/display/example basic
 * @access	public
 * @return	bool
 * @param 	object|int	$schema	The ID or object of the data to use
 * @param	string		$name	Names the entry in the template data array
 * @param	array		$fields	Will display these fields if any are supplied
 */
public function addFormField ($meta, $data, $name = 'default') {
		
	if (is_array($meta)) {
		// $meta is the meta if it's an array
		$titleEncrypted = $meta['title'];
		
		if ($this->encrypt_field_name === true) {
			$options = array(
				'cost'	=>	10
			);

			//$metaField_original = $metaField;
			//$titleEncrypted = $this->encrypt_field_name($meta['title']);
		}
		
		$title = $meta['title'];
		$type = $meta['type'];
		
		foreach ($meta as $key => $value) {
		
			if (strpos($key, 'data-') !== false) {
				$key = substr_replace($key, '', 0, 5);
				$this->data['form'][$name][$titleEncrypted]['meta']['data'][$key] = $value;
			} else {
				$this->data['form'][$name][$titleEncrypted]['meta'][$key] = $value;
			}
		}
		
		$this->data['form'][$name][$titleEncrypted]['data']['value'] = $data;
	}
}

/**
 * Generates a result set
 * 
 * Accepts an object that contains 'schema' and 'data' arrays. Will also accept
 * an ID of a registry resultset (depreciated, objects should be passed
 * instead).
 * 
 * @see	general topics/views
 * @access	public
 * @return	bool
 * @param 	object|int	$id		The ID or object of the data to use
 * @param	string		$name	Names the entry in the template data array
 * @param	array		$fields	Will display these fields if any are supplied
 */
public function addResult ($id, $name = 'default', $fields = array ()) {	
	(array) $tempArray = array ();
	(array) $specialArray = array ();
		
		
	if (is_object($id)) {
		$tempArray['joinSpec'] = $id->schema['joinSpec'] ? $id->schema['joinSpec'] : array();
		$tempArray['fieldSpec'] = $id->schema['fieldSpec'];
		$tempArray['tableSpec'] = $id->schema['tableSpec'];
		$tempArray['data'] = $id->data;
		$tempArray['page'] = $id->schema['page'];
	} else {
		$tempArray = Ivy_Registry::getInstance()->selectData($id);
	}

	if (isset($tempArray['page'])) {
		$this->data['result'][$name]['page'] = $tempArray['page'];
	}
	
	$tempArray['fieldSpec'] = array_merge($tempArray['fieldSpec'], $tempArray['joinSpec']);
	unset($tempArray['joinSpec']);


	
	/* Back to only add fields that you specify (if you specify any)
	 * this is done to prevent collisions between the addForm and addResult
	 * methods
	 */
	if (!empty($fields)) {
		foreach ($fields as $field) {
			$data['fieldSpec'][$field] = $tempArray['fieldSpec'][$field];
		}
		
		$data['tableSpec'] = $tempArray['tableSpec'];
		$data['data'] = $tempArray['data'];
	} else {
		$data = $tempArray;
	}
	
	if (isset($data['tableSpec']['search'])) {
		$data['tableSpec']['search'] = array_change_key_case($data['tableSpec']['search'], CASE_LOWER);
	}
	
	foreach($data['tableSpec']['pk'] as $key => $value) {
		$data['tableSpec']['pk'][$key] = $data['tableSpec']['name'] . '.' . $value;
	}
	
	if (isset($data['data'])) {
		$data['fieldSpec'] = array_change_key_case($data['fieldSpec'], CASE_LOWER);
		
		/**
		 * quick alias to reference the table name
		 */
		(string) $tableName = strtolower($data['tableSpec']['name']);

		foreach ($data['data'] as $record => $array) {
			$record = (isset($this->data['result'][$name]['data']) ?
				count($this->data['result'][$name]['data']) : 0);

			$array = array_change_key_case($array, CASE_LOWER);

			foreach ($data['fieldSpec'] as $field => $fieldSpec) {
			//$value = '';
				//$value = $array[$field];
				$value =  $array[$field];

				//if (!$data['fieldSpec'][$field]) {
				//	continue;
				//}
				
				// 24/04/12 - checks the model->search array for fields to assign
				if (isset($data['tableSpec']['search'])) {
					foreach ($data['tableSpec']['search'] as $searchKey => $searchColumn) {
						if ($field == $searchColumn) {
							$this->data['result'][$name]['data'][$record]['_' . $searchKey] = $value;
						}
					}
					
					//if (isset($data['tableSpec']['search']['ICON'])) {
					//	if ($field == $data['tableSpec']['search']['ICON']) {
					//	echo 'woody';
					//	$this->data['result'][$name]['data'][$record]['_ICON'] = $this->data['result'][$name][ $data['tableSpec']['search']['_icon'] ];
					//}
					//}
					
					
					if (isset($data['tableSpec']['search']['CONTROLLER'])) {
						$this->data['result'][$name]['data'][$record]['__CONTROLLER'] = $searchColumn;
					}
							  				
					if (isset($data['tableSpec']['search']['ACTION'])) {
						$this->data['result'][$name]['data'][$record]['__ACTION'] = $searchColumn;
					}
					
					if (isset($data['tableSpec']['search']['S'])) {
						$this->data['result'][$name]['data'][$record]['__S'] = $searchColumn;
					}
				}

				if ($field[0] != '_') {	
					
					/*
					 * Below IF created to check for data key=>value pairs which
					 * don't belong there. This may happen due to corruption
					 * from calling a form (where you may want to fill in more
					 * fields in a form than be displayed on a result)
					 */
					if (isset($data['fieldSpec'][$field])) {
						$value = (is_array($value) ? implode(', ', $value) : $value);
						$this->data['result'][$name]['data'][$record][$field] = nl2br($value);

						$a[$field] = 'some value';
						if ($data['tableSpec']['pk'][0] != $field && !isset($data['fieldSpec'][$field]['front']['noview'])) {
							
							$this->data['result'][$name]['meta'][$field]
								= (isset($data['fieldSpec'][$field]) ? $data['fieldSpec'][$field]['front'] : $data['fieldSpec']['"' . $field . '"']['front']);
						}
					
						if (isset($data['fieldSpec'][$field]['front']['show']) && $data['fieldSpec'][$field]['front']['show'] == 'y') {
								
							$this->data['result'][$name]['meta'][$field] 
								= (isset($data['fieldSpec'][$field]) ? $data['fieldSpec'][$field]['front'] : $data['fieldSpec']['"' . $field . '"']['front']);
						}

					}
				}					
			}
			
			if (isset($array['_' . strtolower($data['tableSpec']['pk'][0]) ])) {

				$this->data['result'][$name]['data'][$record]['_PK'] = $array['_' . $data['tableSpec']['pk'][0]];						
				
				unset($this->data['result'][$name]['data'][$record][ '_' . $data['tableSpec']['pk'][0] ]);
				unset($this->data['result'][$name]['meta'][ $data['tableSpec']['pk'][0] ]);
			
			} else if (isset($array[ strtolower($data['tableSpec']['pk'][0]) ]) || isset($array[$tableName . '.' . strtolower($data['tableSpec']['pk'][0]) ])) {
			
				(string) $tempName = strtolower($data['tableSpec']['pk'][0]);
				
				if (isset($array[$tableName . '.' . $tempName ])) {
					$tempName = $tableName . '.' . $tempName;
				}
				
				$this->data['result'][$name]['data'][$record]['_PK'] = $array[ $tempName ];
				
				if (isset($data['fieldSpec'][ $tempName ]['front']['show'])) {
					
					if ($data['fieldSpec'][ $tempName ]['front']['show'] != 'y') {
						
						unset($this->data['result'][$name]['data'][ $record ][ $tempName ]);
						unset($this->data['result'][$name]['meta'][ $tempName ]);
						
					}
					
				} else {
				
					unset($this->data['result'][$name]['data'][ $record ][ $tempName ]);
					unset($this->data['result'][$name]['meta'][ $tempName ]);
				
				}
			}

			/**
			 * Handles a second primary key! There is a new requirement to have the ability to specify both a primary key such
			 * as an auto increment for the database layer, but also a second unique key to use as a public key so we don't 
			 * expose the DB primary key
			 *
			 * @author 			James Randell <jamesrandell@me.com>
			 * @datemodified	18th July, 2017
			 */
			if (isset($array['_' . strtolower($data['tableSpec']['pk'][1]) ])) {

				$this->data['result'][$name]['data'][$record]['_PK2'] = $array['_' . $data['tableSpec']['pk'][1]];

			} else if (isset($array[$tableName . '.' . strtolower($data['tableSpec']['pk'][0]) ])) {

				$this->data['result'][$name]['data'][$record]['_PK2'] = $array[$tableName . '.' . $data['tableSpec']['pk'][1]];

			}
		
			//$this->data['result'][$name]['data'][$record] = array_replace($data['fieldSpec'], $this->data['result'][$name]['data'][$record]);	
		}
		
	}
	
	if (!empty($fields)) {
		$temp = $this->data['result'][$name]['meta'];
		$this->data['result'][$name]['meta'] = array ();
		$this->data['result'][$name]['meta'] = array_replace($data['fieldSpec'], $temp);
	}
}

public function addFile ($data, $name = 'server') {
	(array) $array = checkFile($data);	

	switch ($array['ext']) {
		case 'js'	:
			$this->data['system']['html'][] = 
				'<script type="text/javascript" src="' . $array['path'] . '"></script>'."\n";
			break;
			
		case 'css'	:
			$this->data['system']['html'][] = 
				'<link href="' . $array['path'] . '" rel="stylesheet" type="text/css" />'."\n";
			break;
		
		default		:
	}
		
	$this->data['system'][$name][] = $data;
}

/**
 * Alias of addStyle
 * 
 * @access	public
 * @param	string	$stylesheet	The name of the stylesheet to include
 * @return	object
 */
public function addStylesheet ($stylesheet) {
	return $this->addStyle($stylesheet);
}

/**
 * Adds a stylesheet to the template
 * 
 * Looks for the file in the default path, if found then adds it to the display
 * data array, if not then it silently fails.
 * 
 * @access	public
 * @param	string	$stylesheet	The name of the stylesheet to include
 * @return	void
 */	
public function addStyle ($data) {
	(array) $array = checkFile($data);

	switch ($array['ext']) {
		case 'css'	:
			$this->data['system']['style'][] = $array['path'];
			break;
		default		:
	}
}

/**
 * Adds a JavaScript file to the template
 * 
 * Looks for the file in the default path, if found then adds it to the display
 * data array, if not then it silently fails.
 * 
 * @access	public
 * @param	string	$script	The name of the javaScript file to include
 * @return	void
 */
public function addScript ($script) {
	(array) $array = checkFile($script);	

	switch ($array['ext']) {
		case 'js'	:
			$this->data['system']['script'][] = $array['path'];
			break;			
		default		:
	}
}

/**
 * Inserts an array and formats it to JSON
 * 
 * Adds a KSON object to the display.  Could be from a database, XML, JSON or
 * any other related source.
 * 
 * @access	public
 * @return	void
 * @param 	array		$data	Array of data
 */
public function addJSON ($data = array()) {

	$this->output = false;
	$this->header('contentType','json');

	echo json_encode($data);
}

/**
 * Generates a generic piece of data to the display data array
 * 
 * Adds a data object to the display.  Could be from a database, XML, JSON or
 * any other related source.
 * 
 * @access	public
 * @return	void
 * @param 	array		$data	Array of data
 * @param	string		$name	What to call this in the display array
 */
public function addData ($data, $name = 'default') {
	if (isset($this->data['data'][$name]) && 
		!isset($this->data['data'][$name][0])) {
		
		$this->data['data'][$name][0] = $this->data['data'][$name];
			
	} else if (isset($this->data['data'][$name][0])) {
		$this->data['data'][$name][] = $data;
	} else {
		$this->data['data'][$name] = $data;
	}
}

	
	/**
	 * Appends the navigation array bypassing the registry
	 *
	 * @param	$array		array	navigation elements
	 * @return	void
	 */
	public function addNavigation ($array = array ()) {	
		(array) $result = array ();

		foreach ($array as $key => $data) {
			$data['controller'] = (isset($data['controller']) ? $data['controller'] : $_GET['controller']);
			$data['title'] = (isset($data['title']) ? $data['title'] : $key);
			
			
			$data['action'] = (isset($data['action']) ? $data['action'] : 'index');
			$data['query'] = (isset($data['query']) ? '&' . $data['query'] : '');
			
			if (isset($data['parent'])) {
				$result[ $data['menu'] ][ $data['parent'] ][$key] = $data;
			} else {
				if (!isset($result[ $data['menu'] ][$key])) {
					$result[ $data['menu'] ][$key] = array ();
				}
				$result[ $data['menu'] ][$key] = Ivy_Array::merge($data, $result[ $data['menu'] ][$key]);
			}
		}
		
		$this->data['navigation'] = (isset($this->data['navigation']) ?
			Ivy_Array::merge($this->data['navigation'], $result) : $result);
	}
	
	
	/**
	 * Removes a text node based on the $id
	 *
	 * @param	$id			int		id of the text node in the registry
	 * @return	void
	 */
	public function removeText ($id = NULL)
	{
		if ($id !== NULL) {
			unset($this->data['content'][$id]);
		} else {
			$this->data['content'] = array ();
		}
	}
	
	/**
	 * Removes a form node based on the $id
	 *
	 * @param	$id			int		id of the form node in the registry
	 * @return	void
	 */
	public function removeForm ($id = NULL)
	{
		if ($id) {
			unset($this->data['form'][$id]);
		} else {
			$this->data['form'] = array ();
		}
	}
	
	/**
	 * Updates a text node.  This is usefull so that instead of recreating a text node and placing 
	 * the new node at the end, this changes an existing node keeping the same order in the display
	 *
	 * @param	$id			int			id of the text node in the registry
	 * @param	$text		string		the text in the node to update
	 * @param	$array		array		array
	 * @return	void
	 */
	public function updateText ($id, $text, $array = NULL) {
		$this->data->content[$id]->data = $text;
		if (isset($array)) {
			foreach ($array as $key => $value) {
				self::$data->content[$id]->meta->$key = $value;
			} 
		}
	}
	
	
	public function addWidget ($name, $block = 'default') {
		
		$this->data['system']['block'][$block][] = $name;
		
	}

	/**
	 * Runs at the end of script execution (it's one of the last things to be called)
	 *
	 * @param	$template			string		name of the local template to use
	 * @param	$globalTemplate		string		name of the global template to use
	 * @return	void
	 */
	public function build ($template = null, $globalTemplate = null) {
		//self::$instance->buildParameters();
		$this->buildParameters();
		
		(int) $i = 0;
		(array) $settingsArray = array ();		
		(string) $template = strtolower($template);
		(string) $globalTemplate = strtolower($globalTemplate);
		(array) $regArray = $this->registry->selectSystem('other');
		(array) $config = $this->registry->selectSystem('config');
		
		$regArray['action'] = $regArray['action'] ? $regArray['action'] : 'index';
		$regArray['controller'] = $regArray['controller'] ? $regArray['controller'] : 'index';
		
		if (!$template) {
			$template = $this->stylesheet;
		}
		

		if (isset($this->data['system']['script'])) {
			
			$cachedScript = Ivy_File::select(SITEPATH . '/site/' . SITE . '/resource/cache/script');
						
			$js = '';
			foreach ($this->data['system']['script'] as $key => $value) {
				$js .= file_get_contents($value);
			}
		}
		
		
		if (!$globalTemplate) {
			$globalTemplate = $this->globalstylesheet;
		}
		
		
		/*
		 * Conditional resources.  Looks for resource files that share the same 
		 * name as the controller and loads them.
		 */
		$this->addStyle($regArray['controller'] . '.css');
		$this->addScript($regArray['controller'] . '.js');
		
	
		if (isset($this->data['result'])) {
			foreach ($this->data['result'] as $key => $value) {
				if (!isset($value['meta'])) {
					unset($this->data['result'][$key]);
				}
			}
		}
		$this->engine = (empty($this->engine) ? $config['output']['type'] : $this->engine);
		
		$this->data['system']['stat']['server']['script'] = $regArray['script'];
		$this->data['system']['stat']['server']['controller'] = $regArray['controller'];
		$this->data['system']['stat']['server']['action'] = $regArray['action'];
		
		/**
		 * Jan 2010
		 * 
		 * Set default action and controller values
		 */
		$_GET['action'] = $_GET['action'] ? $_GET['action'] : 'index';
		$_GET['controller'] = $_GET['controller'] ? $_GET['controller'] : 'index';
		
		$this->data['system']['stat']['query'] = $_GET;
		$this->data['controller'] = $regArray['controller'];
		$this->data['action'] = $regArray['action'];

		if ($template !== FALSE) {

			if (is_readable(SITEPATH . '/extension/' . EXTENSION . '/view/local/' . $template . '.htm')) {

				$settingsArray['localPath'] = SITEPATH . '/extension/' . EXTENSION . '/view/local/';
			
			} else if (is_readable(SITEPATH . '/site/' . SITE . '/resource/view/local/' . $template . '.htm')) {								
				$settingsArray['localPath'] = SITEPATH . '/site/' . SITE . '/resource/view/local/';
			
			} else if (is_readable(SITEPATH . '/site/' . SITE . '/view/local/' . $template . '.htm')) {				

				$settingsArray['localPath'] = SITEPATH . '/site/' . SITE . '/view/local/';
			
			} else if (is_readable('shared/' . THEME . '/view/local/' . $template . '.htm')) {

				$settingsArray['localPath'] = 'shared/' . THEME . '/view/local/';

			} else if (is_readable( SITEPATH . '/shared/' . THEME . '/view/local/' . $template . '.htm')) {

				$settingsArray['localPath'] = SITEPATH . '/shared/' . THEME . '/view/local/';					
			
			} else if (is_readable($template . '.htm')) {

				$y = explode('/', $template);
				$template = $y[ count($y)-1 ];
				unset($y[ count($y)-1 ]);
				$y = implode('/', $y);
				$settingsArray['localPath'] = $y . '/';	
			
			} else {				
				
				trigger_error('The local template can\'t be found:' . $template);
				$settingsArray['localPath'] = 'shared/' . THEME . '/template/local/';
				$template = 'index';				
			
			}

			if (defined('EXTENSION') && is_readable(SITEPATH . '/extension/' . EXTENSION . '/view/global/' . $globalTemplate . '.htm')) {
		
				$settingsArray['globalPath'] = SITEPATH . '/extension/' . EXTENSION . '/view/global/';
			
			} else if (is_readable(IVYPATH . '/extension/' . $this->extension . '/view/global/' . $globalTemplate . '.htm')) {
		
				$settingsArray['globalPath'] = IVYPATH . '/extension/' . $this->extension . '/view/global/';
			
			} else if (is_readable(SITEPATH . '/site/' . SITE . '/resource/view/global/' . $globalTemplate . '.htm')) {				
				
				$settingsArray['globalPath'] = SITEPATH . '/site/' . SITE . '/resource/view/global/';				
			
			} else if (is_readable(SITEPATH . '/site/' . SITE . '/view/global/' . $globalTemplate . '.htm')) {				
				
				$settingsArray['globalPath'] = SITEPATH . '/site/' . SITE . '/view/global/';				
			
			} else if (is_readable('shared/' . THEME . '/view/global/' . $globalTemplate . '.htm')) {
				
				$settingsArray['globalPath'] = 'shared/' . THEME . '/view/global/';

			} else if ( is_readable (  SITEPATH . '/shared/' . THEME . '/view/global/' . $globalTemplate . '.htm' ) ) {
				
				$settingsArray['globalPath'] =  SITEPATH . '/shared/' . THEME . '/view/global/';
			
			} else {				
				
				trigger_error('The global template can\'t be found:' . $globalTemplate);
				$settingsArray['globalPath'] = 'shared/' . THEME . '/template/global/';
				$template = 'default';				
			
			}

			
			
			$errorArray = $this->registry->selectError('validation');
			/**
			 * if there are validation issues with the form data we need to assign them to the view.
			 * we also need to loop through the forms to find out which errors go with which forms!
			 */
			if ($errorArray !== FALSE) {
				foreach ($this->data['form'] as $formName => $formData) {
					foreach ($errorArray as $key => $data) {

						$encryptedKey = $this->encrypt_field_name($key);

						if (isset($this->data['form'][$formName][$encryptedKey])) {
							$this->data['form'][$formName][$encryptedKey]['error'] = $data;
							unset($this->data['form'][$formName][$key]);
						}
					}
				}
				
			}
			
			$errorArray2 = $this->registry->selectError('other');
			
			if ($errorArray2 !== false) {
				$i = 0;
				foreach ($errorArray2 as $key => $data) {
					if ($data['type'] == 'user') {
						$this->data['error'][$i] = $data;
						++$i;
					}
				}
			}
			
			$this->extra();
			$this->language();

			
			$this->data['system']['var']['totaltime'] = timerSelect();
			
			if (isset($_GET['ivy_debug'])) {
				$settingsArray['globalPath'] = 'shared/ivy/template/global/';
				$globalTemplate = 'debug';
				$settingsArray['localPath'] = 'shared/ivy/template/local/';
				$template = 'ajax';
			}

			switch ($this->engine) {
				case	'borne'		:
					if (!class_exists('Ivy_Borne')) {
    					die('Ivy_Borne does not exist');
					}
					$templateEngine = new Ivy_Borne($this->data, $settingsArray);
					#$templateEngine = Ivy_Borne::getInstance($this->data, $settingsArray);
					break;
				case	'smarty'	:
					if (!class_exists('Ivy_Smarty')) {
    					die();
					}

					$templateEngine = Ivy_Smarty::getInstance($this->data, $settingsArray);
					break;
				case	'xsl'		:
					if (!class_exists('Ivy_Xsl')) {
    					die();
					}

					$templateEngine = Ivy_Xsl::getInstance($this->data, $settingsArray);
					break;
				default	:
					die ('No template engine specified.<br>Please review your applications config file under "output".');
			}
			
			if (isset($_GET['ivy_rest'])) {
				$r = new Ivy_Xml();

				header ("content-type: text/xml");
				echo '<root>' . ArrayToXML($this->data) . '</root>';
				
			} else if (isset($_GET['rss'])) {
				
				echo '<?xml-stylesheet type="text/xsl" href="' . $_GET['ivy_rest'] . '"?><rss version="2.0"><channel>';
				echo '<title>Title</title>';
				echo '<link>link</link>';
				echo '<description>description</description>';
				
				foreach ($this->data['result']['default']['data'] as $key => $value) {
					echo '<item>';
					echo '<title><![CDATA[' . $value[ $_GET['title'] ] . ']]></title>';
					echo '<link><![CDATA[' . $value[ $_GET['link'] ] . ']]></link>';
					echo '<description><![CDATA[' . $value[ $_GET['description'] ] . ']]></description>';
					echo '</item>';
					unset($this->data['result'][$key]['meta']);
				}
				echo '</channel></rss>';
			} else {
				
				$templateEngine->output = $this->output;
				return $templateEngine->parse($template, $globalTemplate);
			}
			
		}
	}
	
/*

	Everything below this point needs cleaning up.  chang the way the registry and display objects are outputted

*/

	
	private function extra () {
		$error = $this->registry->selectError('validation');
		$navigation = $this->registry->selectSystem('navigation');
		if ($error !== FALSE) {
			foreach ($error as $field => $value) {

				/**
				 * James Randell
				 * 27/04/2017
				 *
				 * Commetned out after the encrypted field names went in. This was being called which was adding not only the encrypted
				 * field names but the normal field names in WHEN there was a form validation error
				 * I've set it so that only the encrypted fields get set in the view.
				 */
				//$this->data['form']['default'][$field]['error'] = $value;
			}
		}

		if (isset($this->data['navigation'])) {
			$this->data['navigation'] = Ivy_Array::merge($this->data['navigation'], $navigation);
		} else {
			$this->data['navigation'] = $navigation;
		}
	
		
	}


	public function header ($header, $value) {

		/*
		 * find out if we can work with the header type to turn words into HTTP headers
		 */
		if ($header == 'contentType') {
			$header = 'Content-Type';
			switch ($value) {
				case 'html'		:	$value = 'text/html; charset=UTF-8';			break;
				case 'json'		:	$value = 'application/json; charset=UTF-8';		break;
				default 		:	$value = 'text/html; charset=UTF-8';			break;
			}

		}

		header($header . ': ' . $value);
	}
	
	

	
	
	private function encrypt_field_name ($fieldName) 
	{
		return openssl_encrypt($fieldName, 'AES-256-CBC', 'supersecretkey');
	}

	private function decrypt_field_name ($fieldName) 
	{
		return openssl_decrypt($fieldName, 'AES-256-CBC', 'supersecretkey');
	}

	private function language () 
	{
		$encoding = 'UTF-8';

		if ($this->registry->selectSession('locale')) 
		{
			$locale = $this->registry->selectSession('locale');
		} else {
			$locale = 'en_GB';
			$this->registry->insertSession('locale', $locale);
		}
	
		putenv("LANG=" . $locale); 
		setlocale(LC_ALL, $locale);

		$file = SITE;

		// path to the .MO file that we should monitor
		$filename = SITEPATH . '/site/' . SITE . '/resource/locale/' . $locale . '/LC_MESSAGES/' . $file . '.mo';
		$mtime = filemtime($filename); // check its modification time

		// our new unique .MO file
		$filename_new = SITEPATH . '/site/' . SITE . '/resource/cache/locale/'. $locale . '/LC_MESSAGES/' . $file . '_' . $mtime . '.mo'; 

		if (!file_exists($filename_new)) 
		{ 
			// check if we have created it before
			// if not, create it now, by copying the original
		
			if (!file_exists ( SITEPATH . '/site/' . SITE . '/resource/cache/locale/'. $locale . '/LC_MESSAGES/')) 
			{
    			mkdir ( SITEPATH . '/site/' . SITE . '/resource/cache/locale/'. $locale . '/LC_MESSAGES/', 0777, true);
			}

 			copy ( $filename,$filename_new ) ;
 		}
 		
 		// compute the new domain name
 		$domain_new = rtrim($file . '_' . $mtime);

		// bind it
 		bindtextdomain($domain_new, SITEPATH . '/site/' . SITE . '/resource/cache/locale');
 		bind_textdomain_codeset($domain_new, $encoding);

 		// then activate it
 		textdomain($domain_new);
	
	}	

}

?>
