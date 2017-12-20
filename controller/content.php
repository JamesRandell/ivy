<?php
/**
 * CMS module for IVY3.0.
 *
 * Content Management Module for IVY3.0
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CMS
 */
 
class content_Controller extends admin {

protected $title = 'Content';

public function _base () 
{
	$nav = array (
		221 =>	array (
			'title'	=>	'Show me',
			'action'	=>	'listcontentitems',
		),
		220 =>	array (
			'title'	=>	'View config',
			'action'	=>	'viewconfig',
		),
	);

	return $nav;
}
	
	public function index ()
	{

	}
	
	public function listcontentitems ()
	{

		
		$form = new Ivy_Database('ivy_cms');
		
		$form->order = 'CONTROLLER, ACTION';
		$form->select("SITEID = '" . SITE . "'", array('CMSID','DATECREATED','CONTROLLER','ACTION','TITLE'));
		
		$this->display->addResult($form->id);		
	}
	
	public function detail ($get)
	{
		$s = $get['s'];
	
		$this->stylesheet = 'form';
		
		$form = new Ivy_Database('ivy_cms');
		
		$form->select("CMSID = '$s'");
		
		
			$result = $form->update($_POST);
		if ($form->update() !== FALSE) {
			header('Location: index.php?controller=' . $form->data[0]['CONTROLLER'] . '&action=' . $form->data[0]['ACTION']);
	
		}
		
		
		$this->display->addForm($form->id);
	}
	
	public function viewlinks ($get)
	{
		$s = $get['s'];
		$this->stylesheet = 'formresult';
		
		
		$a['fieldSpec']['CONTROLLER']['options']['where'] = "SITEID = '" . SITE . "'";
		$form = new Ivy_Database('table_global_cms_link');


		
		
		
		if (isset($_POST['submit'])) {
			
			$query = $form->insert($_POST);
		}
		
		
		$this->display->addForm($form->id);
		
		$form->select();
		
		$this->display->addResult($form->id, 'default');
		
		
		
		
		
	}
	
	
	
	
	/**
	  * Loops through all controllers retrieving the methods that can be called via the URL
	  *
	  * Looks at the controller directroy under the current application, uses Reflection to 
	  * find the public and non-inherited methods (URL and application specific ones only please!)
	  *
	  * @access private
	  * @return array		$methodArray	multidimensional array of classes and their methods.
	  */
	private function buildNavigation ()
	{
		
		$fileArray = Ivy_File::readDir('includes/controller/');

		$methodArray = array (); // stores the list of class / methods
		
		foreach ($fileArray as $id => $file) {
			
			$a = explode('.', $file);
			$file = $a[0];
			
			
			require 'includes/controller/' . $file . '.php';
			$class = new ReflectionClass($file . '_Controller');
			
			foreach ($class->getMethods() as $methodID => $data) {
				$method = new ReflectionMethod($file . '_Controller', $data->name);
				
				
				$declaringClassArray = (array) $method->getDeclaringClass();
				if($method->isPublic() && $declaringClassArray['name'] != 'Ivy_Controller') {
					$methodArray[$file][$data->name] = 1;
				}
				unset($method);
			}
			
			unset($class);
			
		}
		
		return $methodArray;
		
		
		
	}
	
	public function content ()
	{
		$this->globalstylesheet ='ivy_extended';
	}
	
	
	public function create ()
	{
		$this->stylesheet = 'form';
		
		$cms = new Ivy_Database('ivy_cms');
		$form = new Ivy_Database('ivy_cms_link');
		
		$this->display->addForm($cms->id);
		
		if (isset($_POST['submit'])) {
			$_POST['SITEID'] = SITE;
			$cms->insert($_POST);
		}
	}
	
}
?>