<?php
/**
 * SVN FILE: $Id: Ivy_Session.php 19 2008-10-02 07:56:39Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Session.php $
 *
 * Session looks through two tables for user information
 * - IVY_USER (contains user name, contace email and password)
 * - IVY_PROFILE (contains all other profile information about the user)
 */

class Ivy_Session {

	/**
	 * Contains reference to the Registry object.
	 *
	 * @access private
	 * @var object
	 */
	private $registry = array ();
	
	/**
	 * Local session array
	 *
	 * @access private
	 * @var object
	 */
	private $session = array ();
	
	/**
	 * Config array
	 *
	 * @access privateq
	 * @var object
	 */
	private $config = array ();
	
	/**
	 * accessed by controller to check if we're authenticated
	 * Enter description here ...
	 * @var int
	 */
	private $authenticated = 0;
	
	
	/**
	 * sets the default authentication strength.
	 * 0 = now authentication needed
	 * 1 = username needed
	 * 2 = username and password needed
	 * @var int
	 */
	private $authenticationLevel = 0;
	
	public $acg = array ();
	public $dcg = array ();
	
	public $authenticationAccess = 'public';
	
	private $group = array ();
		
	/**
	 * Assigns the registry to a class variable and runs the authentication 
	 * process
	 *
	 * Tests the Session Key "loggedin"
	 * If not found then checks the strength of the authentication
	 * set up default user profile/permissions
	 * retrieves user profile
	 * retreives user permissions
	 *
	 * @return	void
	 */
	public function __construct ()
	{
		(object) $this->registry = Ivy_Registry::getInstance();
		(array) $this->config = $this->registry->selectSystem('config');

		if (isset($this->config['authentication']['level'])) {
			(string) $this->authenticationLevel = $this->config['authentication']['level'];
		}
		
		if (isset($this->config['authentication']['access'])) {
			(string) $this->authenticationAccess = $this->config['authentication']['access'];
		}
		
		(object) $this->session = $this->registry->selectSession();
				
		if (isset($this->session['authenticated']) && $this->session['authenticated'] === 1) {
			(bool) $this->authenticated = 1;
		}

		//if (isset($this->session['authenticated']) && $this->session['authenticated'] === 1) {
			(array) $this->acg = $this->session['acg'];
			(array) $this->dcg = $this->session['dcg'];
		//}
//echo '<pre>';
//print_r($this->session);
//print_r($this->acg);echo 1;
//echo '</pre>';
		if ($this->authenticated == 0) {
			//(array) $this->session = $this->defaultSession();

			switch ($this->authenticationLevel) {
				case 0	:
					$this->authenticated = 1;
					
					$this->session = $this->defaultSession();
				
					$this->registry->insertSession($this->session);
					$this->registry->saveSession();
					//$this->session['loggedin'] = 1;
					//$this->session = $this->selectProfile($collar);
					break;
				
				case 1	:
					
					//if (isset($_GET['login'])) {
					//	$this->authenticate($collar, $_GET['password']);
					//}
					
					break;
				
				case 2	://echo 4;
					//$this->display->stylesheet = 'login';
					//$this->display->addWidget('_request_detail', 'content');
					//$this->display->addWidget('login','content');
				
					//if (isset($_POST['COLLAR'], $_POST['PASSWORD'])) {
					//$this->authenticate();
					//}
					
					break;
			}
			
			//if ($this->authenticated == 1) {
			//	$this->session['permission'] = $this->permission($collar);
			//}
		}

		$this->sitePermission ();

		$this->registry->insertSession($this->session);

		$this->registry->saveSession ();
	}
	
	private function sitePermission ()
	{
		
		if (isset($this->session['permission'][SITE]['RANK'])) {
			$this->session['rank'] = $this->session['permission'][SITE]['RANK'];
		}
		if (isset($this->session['permission'][SITE]['GROUPDESCRIPTION'])) {
			$this->session['rankdescription'] = $this->session['permission'][SITE]['GROUPDESCRIPTION'];
		}
	}
	
	
	private function selectProfile ($collar)
	{
		//$profile = (array) $this->defaultSession(); // holds the user profile record
		
		(object) $form = new Ivy_Database('ivy_user');


		$form->select('COLLAR = "' . $collar . '"');

		if (isset($form->data[0])) {
			$profile['collar'] =  $collar;
			$profile['email'] =  $form->data[0]['EMAIL'];
			$profile['fullname'] = $form->data[0]['FIRSTNAME'] . ' ' . $form->data[0]['LASTNAME'] . ' (' . $form->data[0]['COLLAR'] . ')';
			$profile['firstname'] =  $form->data[0]['FIRSTNAME'];
			$profile['lastname'] =  $form->data[0]['LASTNAME'];
			$profile['name'] = $form->data[0]['FIRSTNAME'] . ' ' . $form->data[0]['LASTNAME'];
		}
		return $profile;
	}
	
	
	/*private function permission ($collar)
	{
		(array) $array = array (); // role / group data
		
		(object) $role = new Ivy_Database('ivy_user_group');		
		$role->select('COLLAR = "' . $collar . '"');
		
		if (isset($role->data[0])) {
			foreach ($role->data as $row => $data) {
				$array[ $data['GROUPNAME'] ] = $data;
			}
		}

		return $array;
	}*/
	
	
	

	
	/**
	 * Checks authentication level.
	 */
	//private function authenticationLevel ()	{}
	
	/**
	 * Data spec.
	 */
	private function dataSpec () {}
	
	/**
	 * Grabs cookie values.
	 */
	private function getCookie () {}
	
	/**
	 * Sets cookie values.
	 */
	private function setCookie () {}
	
	/**
	 * Removes a cookie
	 */
	private function deleteCookie () {}
	
	
	/**
	 * Returns user id of current user.
	 */
	private function userid () {}
	
	/**
	 * Inserts a variable to be stored in the session
	 * @param 	string 	@key 	The name of the variable to store in the SESSION
	 * @param 	mixed 	@value 	The name of the variable to store in the SESSION
	 */
	public function insert ($key, $value)
	{
		
		$var[$key] = $value;

	 	$this->registry->insertSession($var);
	 
	}
	
	/**
	 * Selects a variable by its key
	 */
	public function select ($key = null)
	{
		
	 	$result = $this->registry->selectSession();

	 	if ($key) {
	 		return $result[$key];
	 	}

	 	return $result;	 	
	}
	
	/**
	 * Removes a session variable by its key
	 */
	public function delete ($key)
	{
		$var = array('other'=>array($key=>$key));
		
	 	$this->registry->deleteSession(array ('other'=>$key));
	}
	
	/**
	 * Returns the current user form the $_SERVER array.
	 */
	private function whoami ()
	{
		(string) $user = '';

		if (isset($_SERVER['REMOTE_USER'])) {
			$userArray = explode('\\', $_SERVER['REMOTE_USER']);
			$user = $userArray[1];
		} else if (isset($_SERVER['AUTH_USER'])) {
			$userArray = explode('\\', $_SERVER['AUTH_USER']);
			$user = $userArray[1];
		}
		
		return $user;
	}
	
	/** 
	 * Resets the default settings for the user session array.
	 */
	private function defaultSession ()
	{
		$array['name'] = 'Guest';
		$array['email'] = 'Guest User';
		$array['fullname'] = 'Guest User';
		$array['firstname'] = 'Guest';
		$array['lastname'] = 'User';
		//$array['loggedintime'] =  date($this->config['system']['unixformat'], time());
		
		return $array;
	}


	/** 
	 * Process the user login.
	 *
	 * Checks for collar and password fields.
	 */
	public function authenticate ($inOrOut = null)
	{
	
		if ($inOrOut === null) {
			return $this->authenticated;
		}
		
		$this->session->authenticated = $inOrOut;
		
		$a['authenticated'] = $inOrOut;
		
		$this->registry->insertSession($a);
		$this->registry->saveSession();
		
		return $inOrOut;
	}
	
	public function authorise($group = array ())
	{
		$a['acg'] = $group;
		$this->registry->insertSession($a);
		$this->registry->saveSession();
	}
	
	
	public function authenticate_bup ()
	{
		//(array) $array = $this->defaultSession();
		
		
		
		if (isset($_POST['COLLAR'])) {
			$collar = $_POST['COLLAR'];
		} else { $collar = ''; }

		if (isset($_POST['PASSWORD'])) {
			$password = $_POST['PASSWORD'];
		} else { $password = ''; }
		
		if (!empty($collar) && !empty($password)) {
			$login = new Ivy_Database('ivy_user');
			$login->select("COLLAR = '$collar'");

			if ($login->count > 0) {
				
				if ($login->data[0]['PASSWORD'] != sha1(crypt($password, $login->data[0]['SALT']))) {
				
					$this->registry->insertError(array (
						'field'		=>	'PASSWORD',
						'title'		=>	'Password',
						'msg'		=>	'The password you supplied was incorrect',
						'type'		=>	'validation'
					));
				
					return false;
				}
				
				$array['name'] = $login->data[0]['FIRSTNAME'] . ' ' . $login->data[0]['LASTNAME'];
				$array['collar'] = $login->data[0]['COLLAR'];
				$array['fullname'] = $array['name'];
				$array['firstname'] = $login->data[0]['FIRSTNAME'];
				$array['lastname'] = $login->data[0]['LASTNAME'];
				$array['rank'] = 1;
				$array['loggedin'] = 'yes';
				$array['rankdescription'] = 'I am logged in';
				$this->authenticated = 1;
				$this->loggedin = true;
				$this->session = $array;
				
				$this->registry->insertSession($this->session);
				$this->registry->saveSession ();
				
				header('X-Connect-forcerefresh: true');
				//header('Location: http://www.ivyconnect.co.uk/request.php');
				//die();
			} else {
			
				$this->registry->insertError(array (
					'field'		=>	'COLLAR',
					'title'		=>	'Email',
					'msg'		=>	'That user was not found',
					'type'		=>	'validation'
				));
			}
			
			
			return $array;
			//$this->registry->saveSession();
		}
	}
	
	public function logout ()
	{
		if ($this->loggedin === true) {

			$this->registry = Ivy_Registry::getInstance();
			$this->registry->reset();

			$this->session = array ();
			$this->registry->saveSession();
			$this->session = $this->registry->selectSession();
			$this->loggedin = false;
			header('X-Connect-forcerefresh: true');
		}
	}
	
	private function password ()
	{
		$random = rand(12345,67890);
		
		$salt = sha1($random . $_SERVER['SERVER_NAME'] . date());
		echo $salt;
		echo '<br>';
		$password = sha1(crypt('password', $salt));
		echo $password.'<br><br>';

	}
}
?>