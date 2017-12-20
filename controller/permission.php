<?php
/**
 * Admin module for IVY3.0.
 *
 * Admin type methods for users to control the application: permissions, styles, content.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Admin
 */
 
class permission_Controller extends admin {

protected $title = 'Permissions';

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

public function index ()
	{

	}
	
	public function view ()
	{
		$a['fieldSpec']['GROUPID']['options']['where'] = " GROUPNAME = '" . SITE . "'";
		$usergroup = new Ivy_Database('ivy_user_group', $a);
		
		if (isset($_POST['submit'])) {
			$query = $usergroup->insert($_POST);print_r($query['error']);
		}
		
		$this->display->addForm($usergroup->id);
		$usergroup->select("GROUPNAME = '" . SITE . "'");
		
		$this->display->addResult($usergroup->id, 'default');
		$this->stylesheet = 'ivy_permissions';
	}
	
	public function detail ($get)
	{
		$id = $get['s'];
		
		$systemArray = $this->registry->selectSystem('config');
		$a['fieldSpec']['GROUPID']['options']['where'] = " GROUPNAME = '" . SITE . "'";
		$form = new Ivy_Database('ivy_user_group', $a);
		
		if (isset($_POST['submit'])) {
			if ($_POST['submit'] == 'Delete') {
				if ($form->delete() !== FALSE) {
					$_SESSION = array ();
					
				}
			} else {
				if ($form->update() !== FALSE) {
					
					
				}
			}

			$_SESSION['ivy_registry'] = array ();
		$this->redirect('view');
		}
		
		$r = $form->select("USERGROUPID = '$id'");
		$this->display->addForm($form->id);

		$this->stylesheet = 'formadv';
	}

	public function groups ($get)
	{
		$this->stylesheet = 'result';
		
		
		$form = new Ivy_Database('ivy_group');
		
		$form->select("GROUPNAME = '" . SITE . "'");
		$this->display->addResult($form->id);
		$this->display->special('default', array('action' => 'groups'));
		
	}
	


} 

?>
