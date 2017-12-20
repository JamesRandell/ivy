<?php

$array = array (
	'fieldSpec'	=> array (
		'EXTENSIONID'	=> array (
			'front'		=> array (
				'title'		=>	'Extension ID',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		),
		'NAME'	=> array (
			'front'		=> array (
				'title'		=>	'Name',
				'type'		=>	'hidden',
				'tip'		=>	'The system id of the extension.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255,
				'required'	=>	'y',
			)
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Install date',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'default'	=>	'date',
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'AUTOLOAD'	=> array (
			'front'		=> array (
				'title'		=>	'Auto load',
				'type'		=>	'select',
				'tip'		=>	'Will this extension run on every request?'
			),
			'back'		=> array (
				'type'		=>	'int',
				'arraytouse'=>	'ivy_bool',
				'size'		=>	1
			)
		),
		'SITE'	=> array (
			'front'		=> array (
				'title'		=>	'Site',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			)
		),
		'ACTIVE'	=> array (
			'front'		=> array (
				'title'		=>	'Active?',
				'type'		=>	'select',
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'ivy_bool',
				'size'		=>	1
			)
		),
		'CONTROLLER'	=> array (
			'front'		=> array (
				'title'		=>	'Controller',
				'type'		=>	'text',
				'tip'		=>	'When a URL controller points to this value, this extension is loaded'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_EXTENSION',
		'pk'	=>	array('EXTENSIONID'),
		'page'	=>	20,		
	),
	
	'databaseSpec'	=> array (

	)
);
?>
