<?php
/**
 * Admin module for IVY3.0.
 *
 * Various reset methods.  Include things like delete all cached files, delete non-standard templates, remove all images, rebuild navigation.
 *
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 0.1
 * @package Admin
 */
 
class reset_Controller extends admin {

protected $title = 'Reset';

public function _base () 
{
	$nav = array (
		21 =>	array (
			'title'	=>	'View config',
			'action'	=>	'viewconfig',
		),
		20 =>	array (
			'title'	=>	'View config',
			'action'	=>	'viewconfig',
		),
	);

	return array ();
}

	public function index ()
	{

	}
	
	/**
	 * Loops through the $_POST array and runs a method if a match is found.
	 */
	public function process ()
	{		
		foreach ($_POST as $key => $value) {
			
			$string = '';
			switch ($key) {
				case 'DELETETEMPLATECACHE'		:
					$string = $this->deleteTemplateCache();
					break;
				case 'DELETESESSION'			:
					$string = $this->deleteSession();
					break;
			}
			
			$this->display->addText('<strong>' . $string . '</strong>');
			
			
		}
		$this->display->addText('<h2>Complete!</h2>');
	}
	
	private function deleteTemplateCache ()
	{
		$string = 'Deleting template cache...<br />';
		
		$path = 'site/' . SITE . '/resource/cache/template/';
		if ($handle = opendir($path)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					if (!@unlink($path . $file)) {
						$string .= '&nbsp;Unable to delete ' . $file . ', check permissions.<br />';
					}
				}
		    }
		}
		return $string;
	}

	private function deleteSession ()
	{
		$_SESSION = array ();
		return 'Session data deleted!';
	}
	
	

}
?>