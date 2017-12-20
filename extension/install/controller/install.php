<?php

class install {
	
public function __construct () {

	//parent::__construct();
	$this->view->globalstylesheet = 'app2';

	echo 'This is the install.php::__construct() method';
}

public function test () {

	echo 'this is a test method!';
}

public function index () {}
public function _login () {}
public function _logout () {}
public function _denied () {}

}


?>