<?php
class Ivy_Admin_Install_Controller extends Ivy_Controller {

	
	
public function index ()
{

	if (Ivy_File::select('system/config.ini') === FALSE) {
			
		$this->display->addText('<h1>New install</h1>');
			
			
			$this->display->addText('<p><a href="?controller=ivy_admin_install&action=stageone">Click to start</a></p>');
			
			
			
			
	}
	
				
}


public function stageone ($get)
{

	(int) $s = (isset($get['s']) ? $get['s'] : 0);
	
	$this->stylesheet = 'form';
	

			
	$this->display->addText('<h2>Stage one: Database connection</h2>');
	$id = $this->display->addText('<p>When you submit this form IVY will attempt to connect to the database.  If successful 
		you will progress to stage two, if not then you will have a chance to alter your settings.</p>');
	
	$obj = new Ivy_Ini('ivy_config');
	
	$obj->select();
	
	

	$this->display->addForm($obj->id, '', array('db_type','db_server','db_database','db_username','db_password'));
	
	if ($obj->insert() !== FALSE) {
		$this->display->addText('<p>Testing database connection</p>');
		$r = new Ivy_Database('ivy_site_config');
	}
			
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
				$this->redirect('stagetwo');
				echo 'f';
			} else {
				$this->display->addText('<p>A connection test to the database was successful!.</p>', array('color'=>'green'));
				$this->display->addText('<p>However the configuration file could not be saved. Please make sure you have set 
				 the "system" folder to write.</p>', array('color'=>'red'));
			}
		}
		
	}
}

public function stagetwo ($get)
{
	(string) $string = '';
	(bool) $error = TRUE;
	
	$this->display->addText('<h2>Stage Two: Database objects</h2>');
	
	if (isset($get['success'])) {
		$this->display->addText('<p>Table have been created, please continue to <a href="?controller=ivy_admin_install&action=stagethree">stage three.</a></p>');
		die();
	}
	
	$this->display->addText('<p>You have connected to a database.  Now we will attempt to create the objects required for IVY to run.</p>');
			
		
		
		
		
	(array) $a = array (); // temp key storage
	
	$schema = Ivy_File::select('core/model');
	
	foreach ($schema as $id => $model) {
		if (!is_file('core/model' . $model)) {continue;}	
		(array) $schemaFileNameArray = explode('.', $model);
		(string) $schemaFileName = $schemaFileNameArray[0];
		
		
		$obj = new Ivy_Database('core/model/' . $schemaFileName);
		
		if ($obj->createTable(TRUE) === TRUE) {
			$this->display->addText("<p>Table from schema '$model' created</p>");
		} else {
			$this->display->addText("<p>Failed to create table from schema '$model'</p>");
		}
		
	}
		
		
		
		
		
		
		
		
		
		/*
		
		$user = new Ivy_Database('ivy_user');
		$user->createTable(TRUE);
		
		$usergroup = new Ivy_Database('ivy_user_group');
		if ($usergroup->createTable(TRUE) !== FALSE) {
			$string .= 'Table user_group created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table user_group<br />';
		}	
		
		$userprofile = new Ivy_Database('ivy_user_profile');
		if ($userprofile->createTable(TRUE) !== FALSE) {
			$string .= 'Table user_profile created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table user_profile<br />';
		}
		
		$group = new Ivy_Database('ivy_group');
		if ($group->createTable(TRUE) !== FALSE) {
			$string .= 'Table group created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table group<br />';
		}
		
		$cms = new Ivy_Database('ivy_cms');
		if ($cms->createTable(TRUE) !== FALSE) {
			$string .= 'Table cms created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table cms<br />';
		}
		
		$navigation = new Ivy_Database('ivy_navigation');
		if ($navigation->createTable(TRUE) !== FALSE) {
			$string .= 'Table navigation created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table navigation<br />';
		}
		
		$siteconfig = new Ivy_Database('ivy_site_config');
		if ($siteconfig->createTable(TRUE) !== FALSE) {
			$string .= 'Table site_config created<br />';
			$error = FALSE;
		} else {
			$string .= 'Failed to create table site_config<br />';
		}
		
		if ($error === FALSE) { // there is no error
			$this->redirect(NULL, NULL, 'success');
			
		}
		$this->display->addText('<p>' . $string . '</p>');
		
		*/
		
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
			$this->redirect('stagefour');
		}
	}

}

public function stagefour ()
{
	$this->display->addText('<h2>Stage four: grab a coke, because you\'re done.</h2>');	
	
	$this->display->addText('<p>Everything went well and IVY is now ready to be used.<p>');
	
	$this->display->addText('<p>Use <a title="Go to control panel" href="?controller=index">this link</a> to head back to the control panel.<p>');


}

}
?>