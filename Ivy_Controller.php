<?php
/**
 * Sets up a new parent controller
 *
 * The Controller deals with some base functionality that can be used by child
 * controllers.
 * 
 * @category	Ivy
 * @package		core
 * @author		James Randell <james.randell@ivyframework.co.uk>
 * @copyright	2006 - 2009 Ivy
 * @license		https://ivy.svn.sourceforge.net/svnroot/ivy/LICENSE.txt
 * @version		$Id:$
 * @tutorial	http://ivyframework.com/tutorial/Ivy_Controller.php
 */

abstract class Ivy_Controller 
{

/**
 * Stores the registry
 * 
 * @todo		remove the registry use
 * @registry 	object
 */
protected $registry;
	
/**
 * Local stylesheet declaration
 */
protected $stylesheet = 'default';

/**
 * Global stylesheet declaration
 */
public $globalstylesheet = 'default';


protected $title = '';

/**
 * user details db table object
 * 
 * @access	protected
 * @var		object
 */
protected $userDetail = '';

/**
 * stores information form the database pulled out at the constructor for other
 * methods to use
 * 
 * @access	protected
 * @var		array
 */
protected $connect = array ();

public $appCollar = '';

/**
 * database object for the current users navigation bar
 * 
 * @access	protected
 * @var		object
 */
protected $userNavigation = '';

/**
 * is this an ajax request or not? (default is true)
 * 
 * ajax requests generally use a different 'blank' global stylesheet rather than the 
 * one that calls a bunch of style and script files
 * 
 * @access	protected
 * @var		bool
 */
protected $ajax = true;

/**
 * unique id for the current application
 * 
 * @access	protected
 * @var		int
 */
protected $applicationID = null;

/**
 * tells us if this application is online or not
 * 
 * @access	protected
 * @var var
 */
protected $mode = 'online';


/**
 * overall rank for the current user
 * 
 * @access	public
 * @var		int
 */
public $authorised = 0;

/**
 * custom acl for child applications
 * 
 * @access protected
 * @var array
 */
protected $_acl = array ();

/**
 * temp storage for things in the class. First used in _cleanUpNavigation to store breadcrumbs
 * 
 * @access private
 * @var array
 */
protected $temp = array ();



/**
 * Loads the registry
 */
function __construct ($registry = NULL) {

	if (!$registry) {
		$registry = Ivy_Registry::getInstance();
	}
	
	$this->registry = $registry;
	
	if (is_readable(SITEPATH . '/extension/Session.php')) {

		require SITEPATH . '/extension/Session.php';
		$this->session = new Session;

	} else {

		$this->session = new Ivy_Session;

	}
	
	//$this->view = Ivy_View::getInstance();
	$this->view = new Ivy_View();

	
	if (!defined('CONTROLLER')) {
		define('CONTROLLER', $this->controller());
	}
	
	if (!defined('ACTION')) {
		define('ACTION', $this->action());
	}

	/**
	 * application object generated if the user isn't suppossed to be on this script
	 */
	(object) $application = null;

	if (!isset($_GET['controller'])) {
		$_GET['controller'] = 'index';
	}
	
	if (!isset($_GET['action'])) {
		$_GET['action'] = 'index';
	}

	if ($_GET['action'] == 'index') {
		$this->title = $_SESSION['connect']['title'];
	}
	
	if (!isset($_GET['ajax'])) {
		$this->_cLoad();
	}
				
				
	if (isset($_SERVER['PATH_INFO'])) {
		define("CONTROLLER", $_SERVER['PATH_INFO']);
		$this->CONTROLLER = $_SERVER['PATH_INFO'];
	}

	/**
	 * we're going to loop through the $_GET array to find any 'special' paramters such as 'page'
	 * because this array is a single value list only (1/2/3/4/5/6/etc) then we need to look at each one,
	 * see if it matches a special name, then look at the next value in the list for the value.
	 * it effectivly gives us key/value/key/value if we find special words and the value is meaningfull
	 */
	foreach ($_GET as $key => $value) {

		/**
		 * if this value is numeric, lets check the PRECEEDING key to see if it's a special key
		 */
		if (is_numeric($key)) {
			switch ($_GET[ $key - 1 ]) {
				case 'page'	:
					$_GET['page'] = $value;
					unset($_GET[ $key ]);
					unset($_GET[ $key-1 ]);
					break;
				case 'status'	:
					$_GET['status'] = $value;
					unset($_GET[ $key ]);
					unset($_GET[ $key-1 ]);
					break;
				default			:
					if ($_GET[$key-1] != '') {
						$_GET[ $_GET[$key-1] ] = $value;
					}
					
		
			}
		}
		$verbArray = array('yes','no','true','false','on','off');
		if (array_key_exists($key, $verbArray)) {
			//$_GET[ $_GET[$key-1] ] = $value;
		}
	}

	(object) $temp = '';
	
	/**
	 * set some default options to the stylesheets if $_GET variables are present
	 */
	
	$this->view->addWidget('context', 'context');

	$this->view->addWidget(CONTROLLER . '_' . ACTION, 'content');
	//$this->view->addWidget(ACTION, 'content');

	$this->view->addWidget('footer', 'footer');


	/**
	 * tiny hack to tell the template if we have just done somthing, so we can show a success 
	 * message if required. We do this in the application controller so we don't need to do write
	 * it in all the methods
	 */
	if (isset($_GET['success'])) {
		$this->view->setParameter('success', 1);
	}
	
	
	/**
	 * update the correct action so we can keep the client in sync with the server
	 */
	header('X-Connect-action: ' . ACTION);
	
	if (isset($_GET['s'])) {
		header('X-Connect-id: ' . $_GET['s']);
	}

}

private function _cLoad() {

	
	/*
	 * does a child method exist in the CONTROLLER script?
	 * if it does then call it
	 */
	$methodVariable = array($this, '_load');
	if (is_callable($methodVariable) !== false) {
		$this->_load();
	}
	
	return $this->title;
}

public function redirect($action = ACTION, $controller = CONTROLLER, $die = true) {

	if (!$action) {
		//$action = ACTION;
	}
	
	if ($this->ajax === true) {
		//$action .= '/ajax';	
	} else {
		//$action .= '/ajax';
	}

	
	/**
	 * allows the user to return to the referring page. Useful for using 1 action to be 
	 * called from mutliple actions
	 */
	#if (isset($_GET['r'])) {
	#	$action .= isset($_GET['s']) ? '/' . $_GET['r'] . '/' . $_GET['s'] : '&action=' . $_GET['r'];
	#}

	/**
	 * new reload method (a reload is typically called after a form submit where we want to return to the same page perhaps)
	 * this checks if the ACTION/CONTROLELR match the input which they should be default unless the user passes something different
	 * in
	 */

	/**
	 * update to check for SSL or not using referrer
	 *
	 * @author 			James Randell <jamesrandell@me.com>
	 * @datemodified 	19th July, 2017
	 */
	$domain = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?';
	if (strpos($this->registry->system['other']['referer'], 'https://') === false) {
		$domain = (string) 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?';
	}


	if ($action == ACTION && $controller == CONTROLLER) {

		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $this->registry->system['other']['referer']);
		die();
	}
	
	
	#if (!$action && !$controller) {
	#	header('Location: ' . $this->registry->system['other']['script'] . '/' . $other);
	#	die();
	#}


	if (strpos($action, 'http://') !== false) {
		header('Location: ' . $action . '' . $other);
		die();
	}
		
	$controller = (($controller) ? $controller : $this->registry->system['other']['controller']);
	$action = (($action) ? $action : $this->registry->system['other']['action']);
	$other = ($other) ? '/' . $other : null;
	

	header('HTTP/1.1 301 Moved Permanently');
	/*
	if ($_SERVER['HTTPS'] == 'on') {
		header('Location: https://' . $_SERVER['SERVER_NAME'] . '/' . SITE . '.php?' . $controller . '/' . $action . $other);
	} else {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/' . SITE . '.php?' . $controller . '/' . $action . $other);
	}
	*/

    header('Location:' . $domain . $controller . '/' . $action . $other);
    //echo 777;
    //die();
	
	header('X-Connect-action: ' . $action);
	header('X-Connect-redirect: true');
	
	if (isset($_GET['s'])) {
		header('X-Connect-id: ' . $_GET['s']);
	}
	//trigger_error('HTTP Header: Redirect did not work');

	if ($die == true) {
		return;
		die();
	}
	
}

/**
 * Not yet implemented
 * 
 * @todo	maybe place in the Ivy_Session module
 */
function logout () {
	$this->session->logout();
}
	
/**
 * Declare abstract function
 * 
 * All controllers will have an index method defined
 */
abstract function index ();

/**
 * Returns application name or id form the config file based on the current
 * system
 */
protected function sysid () {
	return $this->registry->system['config']['system']['name'];		
}
	
/**
 * Returns the name of the active controller
 */
protected function controller () {
	return $this->registry->system['get']['controller'];		
}
	
/**
 * Returns the name of the current action
 */
protected function action () {
	return $this->registry->system['get']['action'];		
}

/**
 * Returns the previous script URI
 */
protected function referer () {
	return $this->registry->system['other']['referer'];		
}

/**
 * Returns the registry object
 * 
 * @todo	do we need to remove this if we are replacing the registry?
 */
static function load () {
	$registry = Ivy_Registry::getInstance();
	return $registry;
}


/**
 * This is turning more into a bit of a black box where the process goes:
 *	1: Call method
 *	2: Mysterious magic voodoo happens
 *	3: Return a result that somehow, with a prayer and some stern words, is what you want
 *
 * The _permissionsLoop method really loops through each child element of the navigation array (it self-invokes to traverse
 * the entire navigation array), processing the _acg and _dcg arrays to figure out if the user should see and access those actions.
 * It's grown a bit since it was first built earlier in the year (March 2017), and takes a solid 15 minutes to understand what's
 * going on when I re-look at it again.
 *
 * @todo 			Erm, refactor perhaps?
 * @datemodified 	17th August, 2017
 * @author 			James Randell <james.randell@curtisfitchglobal.com>
 * @access			private
 * @return			array
 */
private function _permissionLoop ($array) {

	foreach ($array as $key => $data) {
	
		(bool) $exitLoop = false;

		// by default, always run the loop for children
		(bool) $childrenLoop = true;
		
		if (!isset($data['_acg'])) {
			$array[$key]['_acg'] = $data[$key] = array();
		}
		
		if (!isset($data['_dcg'])) {
			$array[$key]['_dcg'] = $data[$key] = array();
		}
		
		/*if (in_array($this->session->autenticationAccess, $data['_acg']) === 1 && $this->session->authenticate() === 0) {
			unset($array[$key]);
			$exitLoop = true;
		}*/

		if ($this->session->authenticate() === 0) {
			if (!in_array('public', $data['_acg'])) {
				unset($array[$key]);
				$exitLoop = true;
			} else {
				//$childrenLoop = false;
			}
		}

		if ($this->session->authenticate() === 1) {
//echo $data['action'];
//echo 0;
//print_pre($data['_acg']);
//echo 1;
//print_pre($this->session->acg);
//echo '------------------------------------------------<br>';

			if (isset($data['_acg']) && empty(array_intersect($this->session->acg, $data['_acg'])) === true) {
				unset($array[$key]);
				$exitLoop = true;
			}
		
			if (isset($data['_dcg']) && empty(array_intersect($this->session->acg, $data['_dcg'])) !== true) {
				unset($array[$key]);
				$exitLoop = true;
			}
		}

		if ($exitLoop === true) {
			if (isset($data['children'])) {
				//$array[$key]['children'] = $this->_permissionLoop($data['children']);
			}//
			//$this->authorised = 0;
			continue;
		}

		if ($data['controller'] == CONTROLLER && $data['action'] == 'index') {

			$this->view->addParameter('controllertitle', $data['title']);

			if (!empty($data['title'])) {
				$this->temp[ $data['title'] ] = array(	'controller'=> $data['controller'],
										'action'	=> $data['action'],
										'title'		=> $data['title']);
			}
		}

		if ($data['controller'] == CONTROLLER &&  strpos(ACTION, $data['action']) !== false && !empty($data['title']) && $data['action'] != 'index') {
			$this->temp[ $data['title'] ] = array(	'controller'=> $data['controller'],
									'action'	=> $data['action'],
									'title'		=> $data['title']);
		}

#echo $data['controller'].'-'.CONTROLLER.'----------------'.$data['action'].'-'.ACTION.'<br>';

		if ($data['controller'] == CONTROLLER && $data['action'] == ACTION) {
			$this->authorised = 1;
			
			$array[$key]['active'] = true;

			$l = count($this->temp)-1;
			if ($l >= 1) {
				$this->temp[ $data['title'] ]['active'] = true;
			}
			
			$this->view->addParameter('actiontitle', $data['title']);
			$this->view->addParameter('breadcrumb', $this->temp);

			unset($array[$key]['hidden']);
		}

		if (isset($data['children'])) {

			#if ($exitLoop === true) {
			if ($childrenLoop === true) {
				foreach ($data['children'] AS $g => $h) {
					if (!isset($data['children'][$g]['_acg'])) {
						$data['children'][$g]['_acg'] = $data['_acg'];
					}
				}
				
				$array[$key]['children'] = $this->_permissionLoop($data['children']);
			}
			#}
			


			/**
			 * We loop through the results to see if any 'active' flags were added. If they
			 * were then add an active flag to the current key (or parent of the children in this case).
			 * This is so sub-menus bubble up with nav items such as hover menus
			 */
			foreach ($array[$key]['children'] as $id => $value) {
				if (in_array('active', $value)) {
					$array[$key]['active'] = true;
				}
			}
		}
	}
	return $array;
}


/**
 * Cleans up the navigation array depending on how cascading it it
 * 
 * Sets things like active and remove links that the user doesn't have access too.
 * Does this based on if the navigation is SITE wide or just CONTROLLER wide
 * 
 * @param	string	depthType	Either 'site' or 'controller'
 * @author				blah blah blah <my email>
 * @datecreated			2016/10/01
 * @datemodified		2016/10/02
 * @return				void	fdfd
 */
private function _cleanUpNavigation ($depthType = 'site') {
	
	/**
	 * @datecreated	10/03/2017
	 * @author		James Randell <jamesrandell@me.com>
	 *
	 * Partial re-write now that I understand what the hell is going on
	 * because we use the pattern for modular re-use and you can push things higher up the stack
	 * to have more precedence through the framework. I've now coded this to account for multi-site
	 * configurations
	 */
	 
	// we check to see if were looking at the highest level navigation, multisite, or individual site.
	// we pass the navigation array by referrence to make changes easier
	if ($depthType == 'site') {
		$this->navigation[ SITE ]['active'] = true;
	}
	
	(array) $array = $this->navigation = $this->_permissionLoop($this->navigation);
	

	// lets deal with active links and page titles
	//$this->title = ($this->title) ? $this->title : $array[ CONTROLLER ]['title'];
	
	
		
	// now lets look at sub menu items, or children:
	if (isset($array[ CONTROLLER ]['children'])) {
		// To being with, do the title and the active link settings:
		//$array[ CONTROLLER ]['children'][ ACTION ]['active'] = true;
		//$this->title .= ' - ' . $array[ CONTROLLER ]['children'][ ACTION ]['title'];
		
	}
		
	/**
	 * see if the action is a sub action, and has children
	 */
	if (isset($array[ CONTROLLER ]['children'][ ACTION ])) {
		//$array[ CONTROLLER ]['children'][ ACTION ]['active'] = true;
		//$this->title = ($this->title) ? $this->title : $array['title'] 
		//	. '<small>' . $array[ ACTION ]['title'] . '</small>';
	} else {
		//$this->title = $array[ CONTROLLER ]['title'];
	}
}

// called from Ivy_Router after the contructor has run, but before the user method is run
public function _permission () {

	/**
	 * First we do some checks to see how widespread the navigation array goes
	 */
	$this->_cleanUpNavigation();
	
	$this->view->addData($this->navigation, 'navigation');
		
	
	/**
	 * check the session module to see if this is a public or private site
	 */
	if ($this->session->authenticationAccess == 'private') {
		$this->access = true;
	} else {
		$this->access = false;
	}

	return $this->authorised;
}



/**
 * On page destroy build the display
 * important function is this! For here we do a lot of the navigation work
 */	
function __destruct () {
	
	$this->view->setParameter('title', $this->title);
	$this->view->addData($this->context, 'context');

	
	/**
	 * this is new (March 2017). Put in for backwards compatability.
	 * In a perfect worl however, when defining a stylesheet, it's done
	 * in the display/template object, rather than polluting the global 
	 * object namespace
	 */
	if (isset($this->view->stylesheet)) {
		$this->stylesheet = $this->view->stylesheet;
	}
	
	if (isset($this->view->globalstylesheet)) {
		$this->globalstylesheet = $this->view->globalstylesheet;
	}

	$this->view->build($this->stylesheet, $this->globalstylesheet);
}



/**
 * Abstract methods for the sites application_constroller to deal with
 *
 * _login is self explanatory, as is _logout. _denied deals with when the users invockes an action they do not have 
 * permissions to see. Ivy_Router It used to (before August 2017) invoke the _login method, but I've sinse changed this 
 * to better handle templates and instruct the user that they don't have access instead of displaying the login method.
 * 
 * @access	public
 * @return	void
 */
abstract public function _login ();
abstract public function _logout ();
abstract public function _denied ();


/**
 * shortens values in an array by a set amount
 * 
 * if no field is given, it will shorten every value in the array by X
 * 
 * @access	protected
 * @param	mixed	$input	data array whose data will be shortened (or a simple string)
 * @param	int		$length	number of chars to shorten it to
 * @param	string	$field	name of field to shorten (if any)
 * @return	void
 */


protected function trim ($input, $length, $field = null) {
	if (!is_array($input)) {
		if (strlen($input) >= $length) {
			$input = $input . ' ';
			$input = substr($input, 0, $length);
			$input = substr($input, 0, strrpos($input, ' '));
			$input = $input . '...';
		}
	} else {
		foreach ($input as $row => &$data) {
			
			/**
			 * if no field is selected, loop through them all running the trim
			 */
			if ($field === null) {
				foreach ($data as $key => &$value) {
					if (strlen($value) >= $length) {
						$value = $value . ' ';
						$value = substr($value, 0, $length);
						$value = substr($value, 0, strrpos($value, ' '));
						$value = $value . '...';
					}
				}
			} else {
				if (strlen($data[ $field ]) >= $length) {
					$data[ $field ] = $data[ $field ] . ' ';
					$data[ $field ] = substr($data[ $field ], 0, $length);
					$data[ $field ] = substr($data[ $field ], 0, strrpos($data[ $field ], ' '));
					$data[ $field ] = $data[ $field ] . '...';
				}
				
			}
		}
	}
	
	return $input;
}
}
?>