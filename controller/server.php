<?php
/**
 * CMS module for IVY3.0.
 *
 * Content Management Module for IVY3.0
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Navigation
 */
 
class server_Controller extends admin {

protected $title = 'Server';

public function _base () 
{
	
}
	
public function index ()
{
	$this->display->globalstylesheet = 'ajax';
	
	echo phpinfo();
}



}
?>