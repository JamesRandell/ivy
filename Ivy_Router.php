<?php
/**
 * SVN FILE: $Id: Ivy_Router.php 19 2008-10-02 07:56:39Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Router.php $
 */
 
class Ivy_Router {
	
	 /*
	 * @the registry
	 */
	private $registry;

	/**
	 * @the controller path
	 */
	private $path = 'include/controller';

	private $args = array();
	
	public $navigation;
	
	private $get = array ();
	
	private $controller = 'index';
	
	private $action = 'index';
	
	private $pathIvy = __DIR__;


	function __construct () {
		
		(array) $parserArray = array ();
		(array) $array = array ();
		(int) $ivyadmin = 0;
		
		$this->pathSite = $_SERVER['DOCUMENT_ROOT'];


		$parserArray['db']['type'] = 'ini';
		$parserArray['output']['type'] = 'borne';
		$parserArray['output']['theme'] = 'default';
		$parserArray['system']['unixformat'] = 'j M Y, H:i';
		
		$this->registry = Ivy_Registry::getInstance();
		
		$this->getController();


		$file = new Ivy_File();

		$t = $file->load(IVYPATH . '/config/config.ini');
		if ($t === FALSE) {
			$readable = $file->readable(IVYPATH . '/config');
			print_pre($readable);
		}

		


		$parser = parse_ini_file(IVYPATH . '/config/config.ini');

		if ($parser === false) {
			die('Unable to read config file: Ivy/config/config.ini');
		}
		
		
		foreach ($parser as $key => $value) {
			$var = explode('_', $key);
			$parserArray[ $var[0] ][ $var[1] ] = $value;
		}

		$this->registry->insertSystem('config', $parserArray);

		/* AWS PATCH */
		/* Have a look for the aws config key and if it exists we will get the enviroment variables from an AWS 
		elastic enviroment */

		$awsConfig = $this->registry->selectSystem('config') ;

		if ( $awsConfig['db']['aws'] == 1 )
		{
			/* OK assemble an array of the AWS Values, these are pretty fixed */
			$aws = array 
			( 
				'db' => array
				(
					'server' 	=> getenv ( "RDS_HOSTNAME" ),
					'database' 	=> getenv ( "RDS_DB_NAME" ),
					'username' 	=> getenv ( "RDS_USERNAME" ),
					'password' 	=> getenv ( "RDS_PASSWORD" ),
				) 
			) ;

			/* OK push this into the registry */
			$this->registry->insertSystem('config', $aws) ;
		}

		if (is_readable(SITEPATH . '/' . SITE . '/system/array.php')) {

			require	SITEPATH . '/' . SITE . '/system/array.php';
			$this->registry->insertSystem('keys', $array);
		}

		define('THEME', $parserArray['output']['theme']);
		
		$this->registry->insertSystem('config', $parserArray);
		
		
		
		require	IVYPATH . '/system/array.php';			
		$this->registry->insertSystem('keys', $array);

		
		$sessionRegistry = $this->registry->selectSession(0);
	
		
		(string) $debugStr = 'Failed to find files:<br>';
		$debugStr .= ' - ' . SITEPATH . '/' . SITE . '/controller/' . $this->controller . '.php<br>';
		$debugStr .= ' - ' . SITEPATH . '/controller/' . $this->controller . '.php<br>';
		$debugStr .= ' - ' . SITEPATH . '/core/extension/' . $this->controller . '/controller/' . $this->controller . '.php<br>';

		if (is_readable(SITEPATH . '/' . SITE . '/controller/' . $this->controller . '.php')) {
			
			$this->path = SITEPATH . '/' . SITE . '/controller';
		
		} else if (is_readable(SITEPATH . '/controller/' . $this->controller . '.php')) {

			$this->path = SITEPATH . '/controller';
		
		} else if (is_readable(SITEPATH . '/core/extension/' . $this->controller . '/controller/' . $this->controller . '.php')) {
			
			$this->path = SITEPATH . '/core/extension/' . $this->controller . '/controller';
		
		} else {
			
			header("HTTP/1.0 404 Not Found");
			echo $debugStr;
			die ('404 Not Found');
			
		}

		
	}
	
	/**
	 *
	 * @load the controller
	 *
	 * @access public
	 * @return void
	 */
	public function loader () {
		(string) $class = $this->controller . '_Controller';
		(object) $controller = '';
		(string) $action = '';
		(array) $array = array ();
		
		
		require $this->path . '/' . $this->controller . '.php';
		
		/*** a new controller class instance ***/
		$controller = new $class($this->registry);

		/*** check if the action is callable ***/
		(bool) $actionExists = false;
		if (is_callable(array($controller, $this->action)) === false) {
			trigger_error('The action: "' . $this->action . '" was not found');
			$this->action = $action = 'index';
		} else {
			$action = $this->action;
			$actionExists = true;
		}
		
		/* Pass the name of the class, not a declared handler */
		if (isset($this->get)) {
			$array = $this->get;
		}
		
		/**
		 * the _permissions method is run after the __constructors but before we call 
		 * the method we want. It's used to see if the user has permissions to access
		 * the method based on the navigatin array specified in the constructor
		 */
		$controller->_permission();
		
		if ($actionExists === false) {
			trigger_error('The action: "' . $this->action . '" was not found');
			die ('404 '. $this->action . ' not Found');			
		}


		if ($controller->authorised === 0 && $action != 'logout') {
			//$this->session->authenticate(0);

			/**
			 * Changed the called action from _login to _default to better handle when a user tries to access an action they shouldn't
			 *
			 * @datemodified 	17th August, 2017
			 * @author 			James Randell <james.randell@curtisfitchglobal.com>
			 */
			$action = '_denied';
		}

		/*** run the action ***/
		$controller->$action($array);

	}


	/**
	 *
	 * @get the controller
	 *
	 * @access private
	 *
	 * @return void
	 *
	 */
	private function getController () {
		(string) $controller = '';
		(string) $action = '';
		(array) $getArray = array ();
		(array) $otherArray = array ();

		// will contain either REQUEST_URI ot QUERY_STRING
		(string) $uriString = "";
		
		$this->action = 'index';
		
		/**
		 * Because i've been fiddling about with NGINX configurations and rewrite rules, I've added
		 * in some logic to extract URL parts romthe REQUEST_URI if the QUERY_STRING is empty.
		 * This can be empty if I don't pass additional parameters to the php file from the nginx
		 * rewrite rule
		 */
		

		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == "") {
			// QUERY_STRING is empty, so lets check REQUEST_URI which won't be
			$uriString = $_SERVER['REQUEST_URI'];

			/**
			 * we're now going to explode the string, and remove any elements before and including a value
			 * that matches our site name
			 * 
			 * (Nov 2021) We only do this for the REQUEST_URI as it may include URI parts BEFORE the 
			 * controller. For instance, it could include the SITE name we're accessing, in which case we 
			 * want to remove this and everything before it as we have that already defined in 'SITE'
			 */
			(array) $t_uriArray = explode($uriString, '/');
			(int) $t_removePoint = 0;
			foreach ($uriArray as $key => $value) {
				if (SITE === $value) {
					// record where the SITE name was found, so we can remove everything before it in our array
					$t_removePoint = $key;
				}
			}

			$t_uriArray = array_slice($t_uriArray, $t_removePoint-1, NULL, TRUE);
			$uriString = implode('/', $t_uriArray);

		} else {
			$uriString = $_SERVER['QUERY_STRING'];
		}

		
		/**
		 * new part as of April 20th 2010
		 * 
		 * checks the PATH_INFO section of the SERVER global to fingure out the controller
		 * and action, instead of $_GET variables
		 *
		 * July 2011 - edited to allow better URI mapping
		 */

		if ($uriString != "") {
			$queryParts = explode('/', $uriString);

			/**
			 * check for a start slash and remove the empty value if there is one
			 */
			if (!$queryParts[0]) {
				array_shift($queryParts);
			}

			$sessionArray = $this->registry->selectSession();
			$keys = $this->registry->selectSystem('keys');
			$arrAvailableLocaleList = $keys['locale'];
			
			if (!isset($sessionArray['localeList'])) 
			{
				foreach ($arrAvailableLocaleList as $key => $value) 
				{
					$data =  explode('_', $key);
					$sessionArray['localeList'][$key] = array (	'title'	=> $value,
															'language'	=> $data[0],
															'country'	=> $data[1] ) ;
				}

				$this->registry->insertSession ( $sessionArray ) ;
			}

			// check if the first URL parameter exists in the locale list
			if ($sessionArray['localeList'][$queryParts[0]]) 
			{
				$this->locale = $queryParts[0];
				array_shift($queryParts);
			} else {
				// if it doesn't exist then it may not be a language code, just the name of the controller.
				// in this case don't overwrite the locale, see if there is one in the session
				if (isset($sessionArray['locale'])) {
					$this->locale = $sessionArray['locale'];
				} else {
					
				}
			}

			if ($arrAvailableLocaleList[$queryParts[0]]) 
			{
				$this->locale = $queryParts[0];
			}
					
			$sessionArray['locale'] = $this->locale;
			$sessionArray['language'] = $sessionArray['localeList'][$this->locale]['title'];
			$sessionArray['country'] = $sessionArray['localeList'][$this->locale]['country'];
			$this->registry->insertSession($sessionArray);
		
			$sessionArray = $this->registry->selectSession();
			
			/**
			 * assign controller
			 */
			$this->controller = $_GET['controller'] = ($queryParts[0]) ? $queryParts[0] : 'index';
			array_shift($queryParts);
			
			/**
			 * assign action
			 */
			$this->action = $_GET['action'] = ($queryParts[0]) ? $queryParts[0] : 'index';
			array_shift($queryParts);
			
			
			/**
			 * we find the remaining values and assign them
			 */
			foreach ($queryParts as $key => $value) {
				if ($key === 0 && is_numeric($value)) {
					$this->s = $_GET['s'] = $value;
				}
				
				$temp = $key + 1;
				$this->{$temp} = $_GET[$temp] = $value;
				
				if ($value == 'ajax') {
					$_GET['ajax'] = 'ajax';
				}
			}
			
		} else {
			if (isset($_GET['controller']) && !empty($_GET['controller'])) {
				$this->controller = $_GET['controller'];
			}
			if (isset($_GET['action']) && !empty($_GET['action'])) {
				$this->action = $_GET['action'];
			}
		}
		
		$this->get = $_GET;
		
		$this->get['controller'] = $this->controller;
		$this->get['action'] = $this->action;
		
		
		$otherArray['dateViewed'] = time();
		$otherArray['controller'] = $this->controller;
		$otherArray['action'] = $this->action;

		$arr = $this->registry->selectSystem('other');
		
		if (isset($_SERVER['REQUEST_URI'])) {
			$otherArray['script'] = basename($_SERVER['REQUEST_URI']);
			$fullname = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			$fullname = $_SERVER['SCRIPT_NAME'];
		}
		
		if (!isset($arr['referer'])) {
			
			$otherArray['referer'] = $fullname;
		}
		
		if (isset($_SERVER['HTTP_REFERER'])) {
			
			$arr['referer'] = $_SERVER['HTTP_REFERER'];
			if ($fullname != $_SERVER['HTTP_REFERER']) {
				$otherArray['referer'] = $_SERVER['HTTP_REFERER'];
			} else {
				$otherArray['referer'] = $arr['referer'];
			}
		} else {
			$arr['referer'] = '';
		}

		$this->registry->insertSystem('get' , $this->get);
		$this->registry->insertSystem('other', $otherArray);
	}


}



?>