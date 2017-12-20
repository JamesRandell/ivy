<?php
/**
 * Control panel for IVY3.0.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CP
 */
 
class group_Controller extends admin {

protected $title = 'Groups';

public function _base () 
{
	$nav = array (
		21 =>	array (
			'title'	=>	'View config',
			'action'	=>	'viewconfig',
		),
		20 =>	array (
			'title'	=>	'View config',
			'action'	=>	'viewconfig',
		),
	);

	return $nav;
}
/**
 * Home page for the 'Site' section of the administration panel
 * 
 * @access public 
 * @return	void
 */
public function index ()
{

	
	$obj = new Ivy_Database('ivy_group');
	
	$this->display->addForm($obj->id);
	
	if (isset($_POST['submit'])) {
		$_POST['GROUPNAME'] = S;
	}

	if ($obj->insert() !== FALSE) {
	
	}
	
	$obj->select("GROUPNAME = '" . S . "'");
	
	$this->display->addResult($obj->id);
	$this->display->special('default', array('action'=>'groupedit&site=' . S));
	
}	



public function managegroups ($get)
{

	
}

public function groupedit ($get)
{

	
	$this->display->addText('<h1>Sites</h1>');
	
	$obj = new Ivy_Database('ivy_group');

	if ($obj->update() !== FALSE) {
		$this->redirect('index');
	}
	
	$obj->select("GROUPID = '" . $get['s'] . "'");
	
	
	$this->display->addForm($obj->id);
}



}
?>