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
 
class ivy_admin extends admin {

public function index ()
{
	$nav[0]['title'] = 'Create';
	$nav[0]['action'] = 'create';
	$nav[0]['menu'] = 'context';
	$nav[1]['title'] = 'Existing sites';
	$nav[1]['action'] = 'index';
	$nav[1]['menu'] = 'context';
	
	if (isset($_GET['site'])) {
		$nav[10]['title'] = 'Configuration';
		$nav[10]['action'] = 'config';
		$nav[10]['menu'] = 'context';
		$nav[11]['title'] = 'Groups';
		$nav[11]['action'] = 'managegroups';
		$nav[11]['menu'] = 'context';
		$nav[12]['title'] = 'Permissions';
		$nav[12]['action'] = 'permissions';
		$nav[12]['menu'] = 'context';
		$nav[13]['title'] = 'Navigation';
		$nav[13]['controller'] = 'ivy_admin_navigation';
		$nav[13]['action'] = 'index';
		$nav[13]['menu'] = 'context';
		$nav[14]['title'] = 'Content';
		$nav[14]['controller'] = 'ivy_cms';
		$nav[14]['action'] = 'index';
		$nav[14]['menu'] = 'context';
		$nav[15]['title'] = 'Reset';
		$nav[15]['controller'] = 'ivy_admin_reset';
		$nav[15]['action'] = 'index';
		$nav[15]['menu'] = 'context';
		
	}
	
	if (isset($_GET['site'])) {
		foreach ($nav as $key => $data) {
			$nav[$key]['query'] = 'site=' . $_GET['site'];
			
		}
	}
	$this->navigation->insert($nav);
	$this->display->setParameter('title', 'Sites');
}

}
?>