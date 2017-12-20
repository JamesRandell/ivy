<?php
/**
 * example class to show method visibility
 */
class example_Controller extends application_Controller
{

/*
 * a public method that can be called from the URI
 */
public function index ()
{
	// code goes here
	$this->doSomething();
}

/*
 * a private method that if called from the URI will route back to the index
 * method.
 */
private function doSomething ()
{
	return 'I am doing something';
}

}

?>