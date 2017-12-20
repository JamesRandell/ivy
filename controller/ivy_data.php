<?php
 
class Ivy_Data_Controller extends Ivy_Controller {

	public function index ()
	{
		
		echo file_get_contents('site/' . SITE . '/data.html');
		$this->globalstylesheet = 'ajax';
		die ();
		
	}
	
}
?>