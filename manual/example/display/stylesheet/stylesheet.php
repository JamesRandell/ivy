<?php
/**
 * Changing the stylesheet
 * 
 * There are two parameters that are used when selecting a stylesheet, the
 * global and the local.
 */
public function index ()
{
	/**
	 * Changing the local stylesheet
	 * 
	 * This will be the one you change most as it swaps the functional
	 * stylesheet
	 */
	$this->display->stylesheet = 'default';
	
	/**
	 * Changing the global stylesheet
	 * 
	 * The global stylesheet deals with the general 'C' or inverted L designs
	 * and is rarley changed
	 */
	 $this->display->globalstylesheet = 'default';
}
?>