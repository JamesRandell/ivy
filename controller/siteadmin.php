<?php
class siteadmin extends Ivy_Controller {

protected $title = 'Home';

public function __construct ()
{
	parent::__construct();	
	
	if (isset($_GET['site'])) {
		
		$obj = new Ivy_Database('ivy_site_config');	
		$obj->select("siteid = '" . $_GET['site'] . "'", array('SITEID','SYSTEM_NAME','OUTPUT_THEME'));

		define("S", $_GET['site']);
		
		$nav = array (
			'site' 	=>	array (
				'title'		=>	'Site',
				'controller'=>	'site',
				'action'	=>	'detail',
			),
			'config' 	=>	array (
				'title'		=>	'Configuration',
				'controller'=>	'siteconfig',
			),
			'group' 	=>	array (
				'title'		=>	'Groups',
				'controller'=>	'group',
			),
			'permission'=>	array (
				'title'		=>	'Permissions',
				'controller'=>	'permission',
			),
			'navigation'=>	array (
				'title'		=>	'Navigation',
				'controller'=>	'navigation',
			),
			'content' 	=>	array (
				'title'		=>	'Content',
				'controller'=>	'content',
			),
			'reset' 	=>	array (
				'title'		=>	'Reset',
				'controller'=>	'reset',
			),
		);
		
		foreach ($nav as $key => $data) {
			$nav[$key]['query'] = 'site=' . $_GET['site'];
			$nav[$key]['menu'] = 'context';
			$nav[$key]['parent'] = 'siteview';			
		}
		
		$this->display->setParameter('title', 'IVY Site "' . $obj->data[0]['SYSTEM_NAME'] . '" - ' . $this->title);
	} else {
		$this->display->setParameter('title', 'IVY Admin');
	}
	
	$nav['sitecreate']['title'] = 'Create';
	$nav['sitecreate']['controller'] = 'site';
	$nav['sitecreate']['action'] = 'create';
	$nav['sitecreate']['menu'] = 'context';
	$nav['siteview']['title'] = 'Existing sites';
	$nav['siteview']['controller'] = 'site';
	$nav['siteview']['action'] = 'index';
	$nav['siteview']['menu'] = 'context';
	
	$this->navigation->insert($nav);
	

}

public function index () {}

protected function insertNav ($array)
{
	if (isset($_GET['site'])) {
		foreach ($array as $key => $data) {
			$array[$key]['query'] = 'site=' . $_GET['site'];
		}
	}
	$this->navigation->insert($array);
}
}
?>