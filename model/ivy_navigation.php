<?php

$array = array (
	'fieldSpec'	=> array (
		'NAVIGATIONID'	=> array (
			'front'		=> array (
				'title'		=>	'Navigation ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		),
		'CONTROLLER'	=> array (
			'front'		=> array (
				'title'		=>	'Controller',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	26
			)
		),
		'ACTION'	=> array (
			'front'		=> array (
				'title'		=>	'Action',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	26
			)
		),
		'TITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'text',
				'size'		=>	40
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			)
		),
		'RANK'	=> array (
			'front'		=> array (
				'title'		=>	'Rank',
				'type'		=>	'select',
				'size'		=>	2,
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	3
			),
			'options'		=> array (
				'table'		=>	'ivy_group',
				'key'		=>	'RANK',
				'value'		=>	'GROUPDESCRIPTION',
			),
		),
		'SITEID'	=> array (
			'front'		=> array (
				'title'		=>	'System',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	26
			)
		),
		'MENU'	=> array (
			'front'		=> array (
				'title'		=>	'Menu',
				'type'		=>	'select',
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'ivy_navigationmenu',
				'size'		=>	26
			)
		),
		'WEIGHT'	=> array (
			'front'		=> array (
				'title'		=>	'Weight',
				'type'		=>	'text',
				'size'		=>	1,
				'tip'		=>	'You can adjust the order of the links with the Weight control.  Heavier 
								weights sink to the bottom of the navigation while lighter weights rise to the top.',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_NAVIGATION',
		'pk'	=>	array('NAVIGATIONID'),
	),
	
	'databaseSpec'	=> array (

	)
);
?>
