<?php
/**
 * MIME compatible email class
 *
 * @package    Ivy_Mail
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 1.0
 */
class Ivy_Api {
	

function __construct ($site) {

if (!isset($site)) {
	die('specify a site');
}
//echo '/site/' . $site . '/controller/application_Controller.php';
	if (!is_readable('site/' . $site . '/controller/application_Controller.php')) {
		
		die('API site not found');
	}

	$apiSitePath = 'site/' . $site . '/controller/';

	//$test = function () use ($apiSitePath) {
		
//use application_Controller as MyClass;
		//class_alias('One', 'one');


		//include $apiSitePath . 'index.php';

		
//use MyClass;
		//namespace test;
		//use application_Controller as another;
		//$t = new application_Controller();
		//$q = new application_Controller();
	//};	
	//$test();
}
}
?>