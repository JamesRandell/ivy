<?php
/**
 * check
 * SVN FILE: $Id: Ivy_Controller.php 19 2008-10-02 07:56:39Z shadowpaktu $
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
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Controller.php $
 */
abstract class Ivy_Admin {
	/*
	 * @registry object
	 */
	protected $registry;
	protected $stylesheet = 'default';
	public $globalstylesheet = 'default';

	function __construct ($registry = NULL)
	{
		if (!$registry) {
			$registry = Ivy_Registry::getInstance();
		}
		
		$this->registry = $registry;
		$this->session = new Ivy_Session;
		
		
		
		/*$extension = new Ivy_Database('ivy_extension');		
		$extension->select("SITE = '" . SITE . "'");
		
		foreach ($extension->data as $key => $value) {
			if (Ivy_File::select('extension/' . $value['EXTENSIONID']) !== FALSE) {
				
				
				if ($value['_AUTOLOAD'] == 1) {
					require_once 'extension/' . $value['EXTENSIONID'] . '/' . $value['NAME'] . '.php';
					$class = $value['NAME'] . '_Controller';
					$controller = new $class($this->registry);
				}
			}			
		}
*/
		$this->display = Ivy_Template::getInstance();
		
		// lets get content!
		$form = new Ivy_Database('ivy_cms');
		$form->select("CONTROLLER = '" . $this->controller() . "' AND ACTION = '" . $this->action() . "' AND SITEID = '" . SITE . "'");

		if (isset($form->data[0]['TITLE'])) {			
			#$this->display->addText('<h1>' . $this->navigation->actiontitle . '</h1>', array('class'=>'ivy_edit','id'=>$form->data[0]['CMSID']));
			$this->display->addText($form->data[0]['CONTENT']);
			$this->display->setParameter('metakeywords', $form->data[0]['METATITLE']);
			$this->display->setParameter('metadescription', $form->data[0]['METACONTENT']);
			$this->display->setParameter('title', $form->data[0]['TITLE']);
			$this->display->setParameter('actiontitle', $this->navigation->actiontitle);
			$this->display->setParameter('controllertitle', $this->navigation->controllertitle);
		}
		
		
		

	}

	
	function logout () {}
	

	abstract function index ();

	
	public function Ivy_rank ($get)
	{
		$this->stylesheet = 'editinline';
		$this->globalstylesheet = 'ajax';
		
		$permissions = $this->registry->selectSystem('permission');
		
		echo '<form method="post"><select name="RANK">';
		foreach ($permissions['system'] as $row => $value) {
			echo '<option value="' . $value['RANK'] . '">' . $value['GROUPDESCRIPTION'] . '</option>';
		}
		echo '</select>';
	}
	
	public function Ivy_editinline ($get = array (), $schema)
	{
		$field = $get['field'];
		$formid = $get['s'];

		$form = new Ivy_Database($schema);
	
		$this->stylesheet = 'editinline';
		$this->globalstylesheet = 'ajax';
	
		if (isset($_POST['submit'])) {
			$array = array ();

			$_POST[$get['pk']] = $formid;
			if (!isset($_POST[$field])) {
				$_POST[$field] = '';
			}
			
			$query = $form->update($_POST);			

			if (!isset($query['error'])) {
				$array['error'] = 0;
				$form->select($form->schema['tableSpec']['pk'][0] . " = '" . $formid . "'", array($field));
				$t = (string) $form->data[0][$field];
				$array['value'] = $t;
			} else {
				foreach ($query['error'][$field] as $key => $value) {
					$array['error'] = $value['msg'];
				}
			}

			echo json_encode($array);
			$this->stylesheet = 'none';
			die();
		}

		$form->select($form->schema['tableSpec']['pk'][0] . " = '".$formid."'", array($field));
		$this->display->addForm($form->id, 'default', array($field));
	}

	/**
	 * Returns the collar number of the logged in user
	 */
	protected function collar ()
	{
		return $this->registry->session['collar'];		
	}
	
	/**
	 * Returns the rank of the logged in user for the current system
	 */
	protected function rank ()
	{
		return $this->registry->session['rank'];		
	}
	
	/**
	 * Returns application name or id form the config file based on the current system
	 */
	protected function sysid ()
	{
		return $this->registry->system['config']['system']['name'];		
	}
	
	protected function controller ()
	{
		return $this->registry->system['get']['controller'];		
	}
	
	protected function action ()
	{
		return $this->registry->system['get']['action'];		
	}

	protected function referer ()
	{
		return $this->registry->system['other']['referer'];		
	}
	
	protected function redirect ($action = NULL, $controller = NULL, $other = NULL)
	{
		if (!$action && !$controller) {
			header('Location: ' . $this->registry->system['other']['script'] . '&' . $other);
			die;
		}
		
		if (strpos($action, 'http://') !== false) {
			header('Location: ' . $action . '&' . $other);
			die;
		}
		
		$controller = (($controller) ? $controller : $this->registry->system['other']['controller']);
		$action = (($action) ? $action : $this->registry->system['other']['action']);

		header('Location: index.php?controller=' . $controller . '&action=' . $action . '&' . $other);
	}
	
	function __destruct ()
	{
		if ($this->globalstylesheet != 'default') {
		} else if (strpos($this->controller(), 'ivy_') !== FALSE) {
			$this->globalstylesheet = 'default';
		}
		$this->display->build($this->stylesheet, $this->globalstylesheet);
	}
}
?>