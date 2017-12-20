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
 
class Ivy_Feedback_Controller extends Ivy_Controller {

	public function index ()
	{
		$this->globalstylesheet = 'default';
		$this->stylesheet = 'form';
		$form = new Ivy_Database('table_global_feedback');
		

		
		
		
		if (isset($_POST['submit'])) {
			
			$query = $form->insert($_POST);
			if (!isset($query['error'])) {
				$this->display->addText('Thank you for your feedback.  If appropriate we will contact you shortly.');
				$this->stylesheet = 'default';
				
				$mail = new Ivy_Mail;
				$mail->user('IntranetManagement', 'recipient');
				$mail->user($this->collar(), 'sender');
				$mail->subject('New feedback');
				$mail->content($_POST['CONTENT']);
				
				$mail->send();
				
				die();
			}
		} else {
			$this->display->addText('Please use text box to tell us what you think of this application.  You can use it to report
				faults, improvements and anything else you can think of.');
		}
		
		$this->display->addForm($form->id);
		



		
	}
	
	public function detail ($get)
	{
		$id = $get['s'];
		
		$form = new Ivy_Database('table_global_feedback');
		
		
		$form->select("FEEDBACKID = '$id'");
		
		
		$this->display->addResult($form->id, 'default');
		

		$this->stylesheet = 'resultsingle';
	}
	
	public function results ()
	{
		$form = new Ivy_Database('table_global_feedback');
		$form->select();
		$this->display->addResult($form->id, 'default');
		
		$this->stylesheet = 'results';
	
	}

	
	


} 

?>
