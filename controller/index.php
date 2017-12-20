<?php
/**
 * Control panel for IVY Framework.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package CP
 */
 
class index_Controller extends admin {
	
public function _base () {}

public function index ()
{
		
	if (Ivy_File::select('core/config/config.ini') === FALSE) {
		
		$this->display->addText('<h4>New install</h4>');
		$this->display->addText('<p>IVY has detected that setup has not been completed, to use IVY you will need to run the <a href="?controller=ivy_admin_install">Install script</a> which will do the following:
			<ul>
				<li>Create a config.ini that holds basic parameters</li>
				<li>Creates a series of Database entries on your chosen database type</li>
			</ul>
			</p>');
			
	
	} else {

		$nav[0]['title'] = 'Configuration';
		$nav[0]['action'] = 'config';
		$nav[0]['menu'] = 'context';
		$nav[1]['title'] = 'Sites';
		$nav[1]['action'] = 'index';
		$nav[1]['controller'] = 'site';
		$nav[1]['menu'] = 'context';
		
	}
	

	
	
	
	/*ini_set('SMTP', 'riknt63.glos.nhs.uk');
	
	$mail = new Ivy_Mail();
	
	
	$mail->recipient = array('james.randell@glos.nhs.uk');
	$mail->sender = 'james.randell@glos.nhs.uk';
	$mail->subject = 'test';
	$mail->content('fdfd');
	echo $mail->send();
	*/
	
	$nav = array (
			'website' 	=>	array (
				'title'		=>	'Online resources',
				'query'		=>	'http://www.ivyframework.com',
				'menu'		=>	'context',
			),
		);

	#$this->display->addData($nav, 'context');
	
	
	
}	

}
?>