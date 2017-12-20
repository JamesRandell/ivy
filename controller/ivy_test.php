<?php
/**
 * CMS module for IVY3.0.
 *
 * Content Management Module for IVY3.0
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CMS
 */
 
class ivy_test_Controller extends Ivy_Controller {
	
public function index ()
{
	$this->globalstylesheet = FALSE;
	
	(array) $array = $this->registry->error;


	
	echo '<table style="font-size:70%;color:#eee;">';
	echo '<tr><th colspan="3" style="text-align:center;color:#ddd;font-size:110%;border-bottom:1px solid #ccc;">error report</th></tr>';
	foreach ($this->registry->error['other'] as $key => $data) {
		echo '<tr><th style="color:#d9d;padding-right:1.5em;">' . $data['type'] . '</th><td style="color:#eee;padding-right:1.5em;">' . $data['file'] . ', line <i style="color:#aaa;">' . $data['line'] . '</i></td>' .
				'<td style="color:#eee;">' . $data['str'] . '</td></tr>';		
	}
	echo '</table>';
}

}
?>
