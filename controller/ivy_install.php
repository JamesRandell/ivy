<?php

class Ivy_Install_Controller extends Ivy_Controller {

	public function index ()
	{
		
		$user = new Ivy_Database('ivy_user');
		$user->createTable();
		echo '<br><br>';
		$usergroup = new Ivy_Database('ivy_user_group');
		$usergroup->createTable();
		echo '<br><br>';
		$userprofile = new Ivy_Database('ivy_user_profile');
		$userprofile->createTable();
		echo '<br><br>';
		$group = new Ivy_Database('ivy_group');
		$group->createTable();
		echo '<br><br>';
		$cms = new Ivy_Database('ivy_cms');
		$cms->createTable();
		echo '<br><br>';
		$navigation = new Ivy_Database('ivy_navigation');
		$navigation->createTable();
		echo '<br><br>';
		$siteconfig = new Ivy_Database('ivy_site_config');
		$siteconfig->createTable();
	
	
	
		$this->globalstylesheet = 'ajax';
		
		
	}	
	
}
?>