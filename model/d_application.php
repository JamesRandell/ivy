<?php
$array = array (
	'fieldSpec'	=> array (
		'APPLICATIONID'	=> array (
			'front'		=> array (
				'title'		=>	'Application ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'auto'		=>	'y',
				'size'		=>	10
			)
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Date created',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'unix',
				'default'	=>	'date',
				'size'		=>	10
			)
		),
		'CONTROLLER'	=> array (
			'front'		=> array (
				'title'		=>	'Controller',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'ACTION'	=> array (
			'front'		=> array (
				'title'		=>	'Action',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'TITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'CONTENT'	=> array (
			'front'		=> array (
				'title'		=>	'Description',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	2000
			)
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'Contact',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'default'	=>	'collar',
				'size'		=>	55
			)
		),
		'URI'	=> array (
			'front'		=> array (
				'title'		=>	'URI',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	500
			)
		),
		'ICON'	=> array (
			'front'		=> array (
				'title'		=>	'Icon',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	500
			)
		),
		'ACTIVE'	=> array (
			'front'		=> array (
				'title'		=>	'Active',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	3
			)
		),
		'RANK'	=> array (
			'front'		=> array (
				'title'		=>	'Rank',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	3
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'CONNECT_D_APPLICATION',
		'pk'	=>	array('APPLICATIONID'),
		'page'	=>	250
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>