<?php
abstract class admin extends Ivy_Admin {

protected $title = 'Home';
protected $nav = array ();



public function __construct ()
{	
	parent::__construct();	

	$this->title = 'IVY Framework';


	if (!isset($_GET['controller'])) {
		$_GET['controller'] = 'index';
	}
	
	if (!isset($_GET['action'])) {
		$_GET['action'] = 'index';
	}
	
	$this->display->addWidget($_GET['controller'] . $_GET['action']);
	
	if (Ivy_File::select('core/config/config.ini') === false) {		
		$this->nav['install'] = array (
			'action'	=>	'install',
			'title'		=>	'Install IVY Framework',
			'controller'=>	'install',
		);	
	} else {
		$this->nav['index'] =	array (
			'action'	=>	'index',
			'title'		=>	'Home',
			'controller'=>	'index',
		);
		$this->nav['site'] 	=	array (
			'action'	=>	'site',
			'title'		=>	'Sites',
			'controller'=>	'site',
		);			
		$this->nav['manual'] =	array (
			'action'	=>	'manual',
			'title'		=>	'Manual',
			'controller'=>	'manual',
		);
		$this->nav['api'] =	array (
			'action'	=>	'api',
			'title'		=>	'API',
			'controller'=>	'api',
		);
	}
	

	if (isset($_GET['site'])) {
		
		$obj = new Ivy_Database('ivy_site_config');	
		$obj->select("siteid = '" . $_GET['site'] . "'", array('SITEID','SYSTEM_NAME','OUTPUT_THEME'));

		if (!$obj->data[0]) {
			$this->display->addText('<p>You have selected a Site that does not exist.</p>');
			exit();
		}
		define("S", $_GET['site']);

		
		$this->nav[$_GET['controller']]['children'] = array (
			'site' 	=>	array (
				'title'		=>	$obj->data[0]['SYSTEM_NAME'],				
				'action'	=>	'detail',
				'controller'=>	'site',
			),
			'extension' 	=>	array (
				'title'		=>	'Extensions',
				'action'	=>	'index',
				'controller'=>	'extension'
			),
			'config' 	=>	array (
				'title'		=>	'Configuration',
				'action'	=>	'index',
				'controller'=>	'config',
			),
			/*'error' 	=>	array (
				'title'		=>	'Errors',
				'action'	=>	'index',
				'controller'=>	'error',
			),*/
			'api' 	=>	array (
				'title'		=>	'API',
				'action'	=>	'index',
				'controller'=>	'api',
			)
		);	

		
		foreach ($this->nav[ $_GET['controller'] ]['children'] as $key => &$data) {
			$data['action'] = $data['action'] . '&site=' . $_GET['site'];

		}

		
		$this->display->setParameter(
			'title', $obj->data[0]['SYSTEM_NAME'] . ' - ' . $this->title
		);

	}
		
	if (isset($this->nav[ $_GET['controller'] ])) {
		if (isset($_GET['site'])) {
			$this->nav['site']['active'] = true;
		} else {
			$this->nav[ $_GET['controller'] ]['active'] = true;
		}
	}
		
		
	if (isset($this->nav[ $_GET['controller'] ]['children'][ $_GET['action'] ])) {echo 'd';
		$this->nav[ $_GET['controller'] ]['children'][ $_GET['action'] ]['active'] = true;
		$this->title = $this->nav[ $_GET['controller'] ]['children'][ $_GET['action'] ]['title'];
	} else {
		$this->title = $this->nav[ $_GET['controller'] ]['title'];
	}



	
	$t = $this->_base();

	$siteTemp = (isset($_GET['site']) ? 'site=' . $_GET['site'] : '');
	
	foreach ($t as $key => $data) {
		$t[$key]['query'] = (isset($t[$key]['query']) ? 
			$t[$key]['query'] . '&' . $siteTemp : $siteTemp);
		$t[$key]['menu'] = 'context';
	}
	
	

	

	$this->display->special('default', array('query'=>'site=' . S));

	$t = new Ivy_Database('ivy_site_config');
	//$t->createTable(true);
}


public function index () {}

abstract protected function _base ();

protected function addNavigation ($array)
{
	if (isset($_GET['site'])) {
		foreach ($array as $key => $data) {
			if (isset($array[$key]['query']) && end($array[$key]['query']) != '&') 			{
				$array[$key]['query'] .= '&';
			}
			$array[$key]['query'] .= 'site=' . $_GET['site'];
			if (isset($_GET['extension'])) {
				$array[$key]['query'] .= '&extension=' . $_GET['extension'];
			}
			if (isset($data['action'])) {
				$array[$key]['query'] .= '&subAction=' . $data['action'];
				$array[$key]['action'] = 'admin';
			} else {
				$array[$key]['query'] .= '&subAction=index';
				$array[$key]['action'] = 'admin';
			}
			$array[$key]['controller'] = 'extension';
		}
	}

	$this->display->addNavigation($array);
}

protected function redirect ($action)
{
	parent::redirect($action . '&site=' . $_GET['site']);
}

public function __destruct ()
{
	$this->display->setParameter('title', $this->title);
	
	//$this->nav[CONTROLLER]['active'] = true;

	if (isset($this->nav[CONTROLLER]['children'][ACTION])) {
		$this->nav[CONTROLLER]['children'][ACTION]['active'] = true;
	}
	$this->display->addData($this->nav, 'site_navigation');
	
	foreach ($this->context as $key => &$data) {
		$data['controller'] = $_GET['controller'];
		
		$data['action'] = $_GET['action'];
		
		if (isset($_GET['site'])) {
			$data['action'] .= '&site=' . $_GET['site'] . '&' . $data['query'];
		}
	}
	
	$this->display->addData($this->context, 'context');
	
	parent::__destruct ();
}

}
?>