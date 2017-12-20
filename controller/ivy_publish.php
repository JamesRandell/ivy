<?php
/**
 * Publish module for IVY3.0.
 *
 * Wraps up the application and moves it to another directory or server.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Publish
 */
 
class Ivy_Publish_Controller extends Ivy_Controller {
	
public function index ()
{
	$this->stylesheet = 'ivy_publish_menu';
}
	
public function publish ()
{

#	$livepath =  $this->registry->system['config']['server']['livepath'];		
	#var_dump(opendir('\\\\gcwwweb02\\intranet'));
	#mkdir('Z:\\blah\\');
	
	#Ivy_File::dirCopy('./', $livepath . '\\' . $this->sysid());
	
	$this->display->addText('<h2>Completed!</h2>');
	
	

}
	
}
?>