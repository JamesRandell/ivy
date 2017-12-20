<?php
/**
 * @SVN FILE: $Id: Ivy_Navigation.php 19 2008-10-02 07:56:39Z shadowpaktu $
 *
 * @Project Name : Project Description
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Navigation.php $
 *
 * This class builds the navigation file/db entry based on what pages are viewed. 
 * Instead of creating the page, and creating one or more entries in a navigation file for it 
 * (eg. nav options for the page itself, then it's create function, then view, then detail etc),
 * the class now 'senses' new pages and adds them to the nav file/db.  The entry can then be edited
 * by the programmer to tag up it's user friendly name, options and permissions.
 * This class follows the Singleton pattarn and as such can't be instatiated directly.
 * Use method: getInstance to instantiate.
 */

class Ivy_Navigation
{
	
	public $navigation;
	
	private $controller = '';
	
	private $action = '';
	
	private $sessionRegistry = array ();
	
	private $systemRegistry = array ();
	
	private $controllerPath = 'include/controller/';

	public $controllertitle = '';
	
	public $actiontitle = '';
	
	/**
	 * Expects GET parameters so that it can work on the current script if needed
	 * @access private
	 * @var array
	 */
	public function __construct($get = array ()) 
	{
		$this->controller = $_GET['controller'];
		$this->action = $get['action'];
		
		$this->registry = Ivy_Registry::getInstance();
		
		$this->systemRegistry = $this->registry->selectSystem('config');
		$this->sessionRegistry = $this->registry->selectSession(0);
	}
	
	public function build ()
	{
		$this->loadDB();
	}
	
	private function loadXML ()
	{
		$xmlString = Ivy_File::load($this->navigationFile);
		$tempArray = Ivy_Xml::toArray($xmlString);
		
		$navigationArray = array_values($tempArray);
		unset($tempArray);
		
			
			
		foreach ($navigationArray[0] as $action => $data) {
			
			if ($this->sessionRegistry['rank'] < $data['rank']) {
				unset($navigationArray[$this->controller][$action]);
			}
		}
		$this->registry->insertSystem('navigation', $navigationArray);
		
	}
	
	private function loadDB ()
	{
		$array = array (array());
		
		$db = new Ivy_Database('ivy_navigation');
		
		$db->limit = 99999;
		$db->order = 'WEIGHT ASC';
		$db->select("SITEID = '" . SITE . "' AND RANK <= '" . $this->sessionRegistry['rank'] . "'");
		// { AND MENU <> 'none'} taken out of above query to enable all rows to be pulled out for the breadcrumbs (which arnt defined always on a menu)
		
		foreach ($db->data as $row => $data) {
			if ($data['CONTROLLER'] == $this->registry->system['get']['controller'] &&
				$data['ACTION'] == 'index') {
				$this->controllertitle = $data['TITLE'];
			}
			
			if ($data['CONTROLLER'] == $this->registry->system['get']['controller'] &&
				$data['ACTION'] == $this->registry->system['get']['action']) {
					$this->actiontitle = $data['TITLE'];
				}
			if ($data['_MENU'] != 'none') {
				$array[ $data['MENU'] ][ $data['CONTROLLER'] . $data['ACTION'] ] = array_change_key_case($data);
			}
		}
		if (!empty($array)) {
			$this->registry->insertSystem('navigation', $array);
		}
	}
	
	public function insert ($array = array ())
	{
		(array) $result = array ();
		
		foreach ($array as $key => $data) {
			$data['controller'] = (isset($data['controller']) ? $data['controller'] : $this->controller);
			$data['title'] = (isset($data['title']) ? $data['title'] : $key);
			
			
			$data['action'] = (isset($data['action']) ? $data['action'] : 'index');
			$data['query'] = (isset($data['query']) ? '&' . $data['query'] : '');
			
			if (isset($data['parent'])) {
				$result[ $data['menu'] ][ $data['parent'] ][$key] = $data;
			} else {
				$result[ $data['menu'] ][$key] = Ivy_Array::merge($data, $result[ $data['menu'] ][$key]);
			}
		}
		
		$this->registry->insertSystem('navigation', $result);
	}
	
}

class Ivy_ReflectionMethod extends ReflectionMethod
{
	public function Ivy_getDocComment ()
	{
		$string = $this->getDocComment();

		preg_match_all("/ivy_(.*?)\t(.*?)\n/s", $string, $matches);
		
		foreach ($matches[1] as $id => $key) {
			$matches[1][$id] = trim(strtolower($key));
		}
		
		foreach ($matches[2] as $id => $value) {
			$matches[2][$id] = trim(strtolower($value));
		}
		
		$array = array_combine($matches[1], $matches[2]);
		
		if (isset($array['child'])) {
			$arr = explode(',', $array['child']);
			$array['child'] = array ();			
			array_push($array['child'], $arr);
			$array['child'] = $array['child'][0];
		}
		
		return $array;



	}
}
?>