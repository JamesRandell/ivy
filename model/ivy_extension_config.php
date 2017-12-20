<?php

$array = array (
	'fieldSpec'	=> array (
		'extensionid'	=> array (
			'front'		=> array (
				'title'		=>	'Name',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'required'	=>	'y'
			)
		),
		'title'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'required'	=>	'y'
			)
		),
		'version'	=> array (
			'front'		=> array (
				'title'		=>	'Version',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10,
			)
		),
		'summary'	=> array (
			'front'		=> array (
				'title'		=>	'Summary',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255,
			)
		),
		'core'	=> array (
			'front'		=> array (
				'title'		=>	'Core version',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10,
			)
		),
		
		'author'	=> array (
			'front'		=> array (
				'title'		=>	'Author',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26,
			)
		),
		'datecreated'	=> array (
			'front'		=> array (
				'title'		=>	'Date created',
				'type'		=>	'text',
			),
			'back'		=> array (
				'default'	=>	'date',
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'description'	=> array (
			'front'		=> array (
				'title'		=>	'Description',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	2000
			)
		),
	),
	'tableSpec'	=> array (
		'pk'	=>	array('extensionid'),
	),
	
	'databaseSpec'	=> array (
		'type'			=>	'ini',

	)
);
?>
