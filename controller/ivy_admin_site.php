<?php
/**
 * Control panel for IVY3.0.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CP
 */
 
class ivy_admin_site_Controller extends Ivy_Controller {

private function base ()
{
	$nav[0]['title'] = 'Create';
	$nav[0]['action'] = 'create';
	$nav[0]['menu'] = 'context';
	$nav[1]['title'] = 'Existing sites';
	$nav[1]['action'] = 'index';
	$nav[1]['menu'] = 'context';
	
	if (isset($_GET['site'])) {
		$nav[10]['title'] = 'Configuration';
		$nav[10]['controller'] = 'siteconfig';
		$nav[10]['action'] = 'create';
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
/**
 * Home page for the 'Site' section of the administration panel
 * 
 * @access public 
 * @return	void
 */
public function index ()
{
	$this->base();
	
	$obj = new Ivy_Database('ivy_site_config');	
	$obj->select(NULL, array('SITEID','SYSTEM_NAME','OUTPUT_THEME'));
	
	$this->display->addResult($obj->id);
	$this->display->special('default', array('s'=>'site'));
	
}	


public function create ()
{	
	$this->base();
	$this->display->addText('<h2>Create new site</h2>');
	$this->display->addText('<p>To create a new site the following information is required</p>');
	
	$obj = new Ivy_Database('ivy_site_config');
	
	$this->display->addForm($obj->id, NULL, array('SITEID','SYSTEM_NAME','OUTPUT_THEME','OUTPUT_TYPE'));
	
	if ($obj->insert() !== FALSE) {
		if (Ivy_File::copy('core/system/examplesite', 'site/' . $_POST['SITEID']) !== FALSE) {echo 'f';
			$this->redirect('createsuccess');
		} else {
			$obj->delete();
			$this->display->addText('<p style="color:red;">The site could not be created, please check your file permissions for the folder <strong>/ivy/site/</strong></p>');
		}
	}
}

public function createsuccess ()
{
	$this->base();
	$this->display->addText('<h2>Success!</h2>');
	
	$this->display->addText('<p>Your site has been created! A record has been added to the database 
		and the skeleton file structure has been set up ready for you to begine development.</p>
		<p>You will need to set up two virtual directories to finish the process:
		<ul>
			<li>The first will be the name of your site which will point to the install of IVY, (&lt;yoursitename&gt; --> /ivy)</li>
			<li>The second is a resource path for images and the like that points to the resource folder in 
				your new site (&lt;yoursitename&gt;/resource --> /ivy/site/&lt;yoursitename&gt;/resource)</li>
		</ul>
		</p>');
	
}

public function detail ($get)
{

	$this->base();
	
	#$this->session->insert('qw','gfgfdfd');	
	#$this->session->delete('qw');
	#$this->session->select('q');echo '<br><br>';	
	#print_r(unserialize($_SESSION['ivy_registry']['session']));
	

	
	
	$this->display->addText('<h2>Modify</h2>');
	$obj = new Ivy_Database('ivy_site_config');
	
	if ($obj->update() !== FALSE) {}
	
	$obj->select("SITEID = '" . $get['site'] . "'");
	$this->display->addForm($obj->id);
	

}

public function managegroups ($get)
{
	$this->base();
	$this->display->addText('<h2>Manage groups</h2>');
	$obj = new Ivy_Database('ivy_group');
	
	$this->display->addForm($obj->id);
	
	if (isset($_POST['submit'])) {
		$_POST['GROUPNAME'] = $get['site'];
	}

	if ($obj->insert() !== FALSE) {
	
	}
	
	$obj->select("GROUPNAME = '" . $get['site'] . "'");
	
	$this->display->addResult($obj->id);
	$this->display->special('default', array('action'=>'groupedit&site=' . $get['site']));
}

public function groupedit ($get)
{
	$this->stylesheet = 'ivy_admin_site_cp';
	
	$this->display->addText('<h1>Sites</h1>');
	
	$obj = new Ivy_Database('ivy_group');

	if ($obj->update() !== FALSE) {
		$this->redirect('managegroups&s=' . $get['site']);
	}
	
	$obj->select("GROUPID = '" . $get['s'] . "'");
	
	
	$this->display->addForm($obj->id);
}

public function permissions ($get)
{
	$this->base();
	
	$this->display->addText('<h2>Permisisons</h2>');
	
	$a['fieldSpec']['GROUPID']['options']['WHERE'] = "GROUPID = '" . $get['site'] . "'";
	$obj = new Ivy_Database('ivy_user_group', $a);
	
	if ($obj->insert() !== FALSE) {
	
	}
	$this->display->addForm($obj->id);
	
	$obj->select("ivy_group.groupname = '" . $get['site'] . "'");
	
	$this->display->addResult($obj->id);
	$this->display->special('default', array('action'=>'permissionsedit&site=' . $get['site']));
	
}

public function permissionsedit ($get)
{
	$this->base();
	
	
	$this->stylesheet = 'ivy_admin_site_cp';
	
	
	
	$obj = new Ivy_Database('ivy_user_group');

	if ($obj->update() !== FALSE) {
		$this->redirect('managegroups&s=' . $get['site']);
	}
	
	$obj->select("USERGROUPID = '" . $get['s'] . "'", array('USERGROUPID','GROUPID'));
	
	
	$this->display->addForm($obj->id, NULL, array('USERGROUPID','GROUPID'));

}




}
?>