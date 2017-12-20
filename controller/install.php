<?php
class Install_Controller extends admin {


public function _base ()
{
	$nav = array (
		'stageone' 	=>	array (
			'title'		=>	'Stage one - Config',
			'controller'=>	'install',
			'action'	=>	'stageone',
			'menu'		=>	'context',
		),
		'stagetwo' 	=>	array (
			'title'		=>	'Stage two - Database',
			'controller'=>	'install',
			'action'	=>	'stagetwo',
			'menu'		=>	'context',
		),
		'stagethree' 	=>	array (
			'title'		=>	'Stage thrre - Data',
			'controller'=>	'install',
			'action'	=>	'stagethree',
			'menu'		=>	'context',
		),
		'stagefour' 	=>	array (
			'title'		=>	'Stage four - Complete',
			'controller'=>	'install',
			'action'	=>	'stagefour',
			'menu'		=>	'context',
		),
	);

	$this->display->addNavigation($nav);
	
	
}


public function index ()
{

	if (Ivy_File::select('system/config.ini') === FALSE) {
			
		$this->display->addText('<h4>New install</h4>');
		$this->display->addText('<p>IVY can be installed with on a number of databases.</p>');
		$this->display->addText('<p>To begin, use the context menu to the right.</p>');

	} else {
		$this->display->addText('<p>It appears that IVY is already installed. if this is not the case then please ' .
			'delete the configuration file inside "<IVY>/core/config/"</p>');
	}
			
}


public function stageone ($get)
{

	(int) $s = (isset($get['s']) ? $get['s'] : 0);
	

	$this->display->addText('<h4>Stage one: Configuration</h4>');
	$id = $this->display->addText('<p>When you submit this form IVY will attempt to connect to the database.  If successful 
		you will progress to stage two, if not then you will have a chance to alter your settings.</p>');
	
	$obj = new Ivy_Ini('ivy_config');

	
	
	if ($obj->insert() !== FALSE) {
		$this->display->addText('<p>Testing database connection</p>');
		$r = new Ivy_Database('ivy_site_config');
	}
			
	$this->display->addForm($obj, 'default', array('db_type','db_server','db_database','db_username','db_password'));

	if (isset($_POST['db_type'])) {
		require_once 'core/connections/' . $_POST['db_type'] . '.php';
		$db = call_user_func(array($_POST['db_type'], 'getInstance'));
		
		$dbArray['type'] = $_POST['db_type'];
		$dbArray['server'] = $_POST['db_server'];
		$dbArray['database'] = $_POST['db_database'];
		$dbArray['username'] = $_POST['db_username'];
		$dbArray['password'] = $_POST['db_password'];

		if ($db->connect($dbArray) === FALSE) {
			$this->display->addText('<p>A connection to the database could not be made. Please double check your settings.</p>', array('color'=>'red'));
		} else {
			if ($obj->insert() !== FALSE) {
				#$this->redirect('stagetwo');
				$this->display->addText('<p><a href="index.php?controller=install&action=stagetwo">Click to move on to Stage Two</a></p>', array('color'=>'green'));
			} else {
				$this->display->addText('<p>A connection test to the database was successful!.</p>', array('color'=>'green'));
				$this->display->addText('<p>However the configuration file could not be saved. Please make sure you have set 
				 the <IVY>/core/config/ folder to write.</p>', array('color'=>'red'));
			}
		}
		
	}
}

public function stagetwo ($get)
{
	(string) $string = '';
	(bool) $error = TRUE;
	
	$this->display->addText('<h4>Stage Two: Database objects</h4>');

	if (isset($get['success'])) {
		$this->display->addText('<p>Table have been created, please continue to <a href="?controller=ivy_admin_install&action=stagethree">stage three.</a></p>');
		die();
	}
	
	$this->display->addText('<p>You have connected to a database.  Now we will attempt to create the objects required for IVY to run.</p>');
		
	$this->display->addText('<p><a href="?controller=install&action=stagetwo&build=yes">Create Tables</a></p>');
	
	if (isset($_GET['build'])) {
		
		$schema = Ivy_File::select('core/model');
	
		foreach ($schema as $id => $extension) {
			if (!is_file('core/model/' . $extension) || $extension == 'ivy_config.php'
			|| $extension == 'ivy_extension_config.php') {continue;}
			
			(array) $schemaFileNameArray = explode('.', $extension);
			(string) $schemaFileName = $schemaFileNameArray[0];

			$obj = new Ivy_Database($schemaFileName);

			if ($obj->createTable(true) === TRUE) {
				$this->display->addText("<p>Table from schema '$schemaFileName' created!</p>");
			} else {
				$this->display->addText("<p>Failed to create table from schema '$schemaFileName'!</p>");
			}
			unset($obj);
			
		}
		
		$this->display->addText('<p><a href="index.php?controller=install&action=stagethree">Click to move on to Stage Three</a></p>');
		

	}
}


public function stagethree ($get)
{
	$this->display->addText('<h2>Stage three: Create database records</h2>');	
	
	$this->display->addText('<p>Connection test complete, tables have been created, now we will write some basic information to those tables so that IVY can properly function.</p>');
	$this->display->addText('<p>If your curious, the following records will be created</p>');
	$this->display->addText('<ul>
								<li>A default user profile with admin rights to the IVY administration screens.</li>
							</ul>');
							
	$objUser = new Ivy_Database('ivy_user');
	$objUserProfile = new Ivy_Database('ivy_user_profile');
	
	$array['COLLAR'] = 'admin';
	$array['PASSWORD'] = '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8';
	$array['EMAIL'] = 'something@seomthing.com';
	$array['FIRSTNAME'] = 'admin';
	$array['LASTNAME'] = 'admin';
	$array['DATECREATED'] = '';
	
	$_POST = $array;
	
	if ($objUser->insert() !== FALSE) {
		if ($objUserProfile->insert() !== FALSE) {
			header('Location: index.php?controller=install&action=stagefour');
		}
	}

}

public function stagefour ()
{
	$this->display->addText('<h4>Stage four: grab a coke, because you\'re done.</h4>');	
	
	$this->display->addText('<p>Everything went well and IVY is now ready to be used.<p>');
	
	$this->display->addText('<p>Use <a title="Go to control panel" href="?controller=index">this link</a> to head back to the control panel.<p>');


}

}
?>