<?php

class Ivy_Extension {

private $extension = '';


function index () {}

public function __construct () {

	echo '<br>This is Ivy_Extension::__construct()<br>';
	$arguments = func_get_args();
	$this->registry = Ivy_Registry::getInstance();

	$this->registry->insertSystem('extension', $arguments[0]);
	
	$navigation['index']['children'] = array (
					'test'	=> array (
						'action'	=>	'test',
						'controller'=>	'index',
						'title'		=>	'Home',
						'icon'		=>	'map',
						'authenticated'=>0
					));
	$this->registry->insertSystem('navigation', $navigation);
	if (is_file('core/extension/' . $arguments[0] . '/controller/' . $arguments[0] . '.php')) {
		require_once 'core/extension/' . $arguments[0] . '/controller/' . $arguments[0] . '.php';
	}

	$this->extension = $class = $arguments[0];//. '_Controller';
	
	$this->view = Ivy_View::getInstance();
	$this->view->extension = $arguments[0];


	unset($arguments[0]);

	$arguments = implode(', ', $arguments);
	
	$r = new $class ($arguments);
	$this->r = $r;

	return $r;
	
}




public function __call ($name, $argument) {
	$r = $this->r;
	
echo $name;die();
	$argumentCount = func_num_args();
	switch ($argumentCount) {
		case 1	:
			$r->$name($argument[0]);
			break;
		case 2	:
			$r->$name($argument[0], $argument[1]);
			break;
		case 3	:
			$r->$name($argument[0], $argument[1], $argument[2]);
			break;
		case 4	:
			$r->$name($argument[0], $argument[1], $argument[2], $argument[3]);
			break;
		case 5	:
			$r->$name($argument[0], $argument[1], $argument[2], $argument[3], $argument[4]);
			break;
		default	:
			$r->$name();
	}
	
	foreach ($argument as $key => $value) {
	#	$arguments[ $key ] = null;
		if (!is_array($value)) {
			#$arguements[ $key ] = implode(', ', $value);
		}
	}	
	

	
	foreach ($r as $key => $data) {
		$this->$key = $data;
	}

	
}


public function __destruct () {
	if (isset($this->view->stylesheet)) {
		$this->stylesheet = $this->view->stylesheet;
	}
	
	if (isset($this->view->globalstylesheet)) {
		$this->globalstylesheet = $this->view->globalstylesheet;
	}

	#$this->view->build($this->stylesheet, $this->globalstylesheet);
}

}
?>