<?php
/**
 * AJAX module for IVY3.0.
 *
 * Various generic AJAX controlls
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package AJAX
 */
 
class ivy_ajax_Controller extends Ivy_Controller {
	
	/**
	 * Looks at the user table
	 *
	 * This method is called via a javascript function
	 */
	public function searchCollar ($get)
	{
		$value = $_POST['value'];
		if (isset($value[3])) {
			$table = new Ivy_Database('table_global_user');

			$table->select("UPPER(lastname) like UPPER('" . $_POST['value'] . "%') OR collar like '" . $_POST['value'] . "%'");
		
			foreach ($table->data as $row => $data) {
				echo '<li>' . $data['LASTNAME'] . ', ' . $data['FIRSTNAME'] . '<SPAN style="display:none;">' . $data['COLLAR'] . '</SPAN></li>';
			}
			
			
		} else {
			echo '<li>Enter at least <strong>4</strong> charecters to search</li>';
		}
		$this->globalstylesheet = 'ivy_ajax';
	}
}

?>