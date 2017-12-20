<?php
/**
 * Control panel for IVY3.0.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CP
 */
 
class Ivy_Cp_Controller extends Ivy_Controller {
	
	public function index ()
	{
		$this->stylesheet = 'ivy_cp_menu';
	}	
}
?>