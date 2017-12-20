<?php

$array = array (
	'fieldSpec'	=> array (
		'USERID'	=> array (
			'front'		=> array (
				'title'		=>	'User ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'size'		=>	10,
				'unique'	=>	'y',
				'required'	=>	'y',
				'auto'		=>	'y',
				'type'		=>	'int'
			)
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'Email',
				'type'		=>	'text',
				'size'		=>	80
			),
			'back'		=> array (
				'size'		=>	255,
				'unique'	=>	'y',
				'required'	=>	'y',
				'type'		=>	'var'
			)
		),
		'FIRSTNAME'	=> array (
			'front'		=> array (
				'title'		=>	'Firstname',
				'type'		=>	'text',
				'size'		=>	'80',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	255
			),
		),
		'LASTNAME'	=> array (
			'front'		=> array (
				'title'		=>	'Lastname',
				'type'		=>	'text',
				'size'		=>	'80',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	255
			),
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Datecreated',
				'type'		=>	'hidden',
				'size'		=>	'80',
			),
			'back'		=> array (
				'default'	=>	'date',
				'type'		=>	'unix',				
				'size'		=>	10
			),
		),
	),
	'tableSpec'	=> array (
		'name'		=>	'IVY_USER_PROFILE',
		'pk'		=>	array('USERID'),
		'auto'		=>	0
	),
	
	'databaseSpec'	=> array (

	)
);
?>
