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
				'auto'		=>	'y',
				'size'		=>	10
			)
		),
		'APPLICATIONID'	=> array (
			'front'		=> array (
				'title'		=>	'Application ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10
			),
			'join'		=> array (
				'type'		=>	'INNER',
				'table'		=>	'CONNECT_D_APPLICATION',
				'pk'		=>	'APPLICATIONID',
				'fields'	=>	array('TITLE','CONTROLLER','ACTION','CONTENT','ICON')
			),
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'User name',
				'type'		=>	'text',
				'class'		=>	'autocomplete'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			),
			'replace'	=> array (
				'table'		=>	'USER_DETAIL',
				'key'		=>	'COLLAR',
				'fields'	=>	array('FIRSTNAME','LASTNAME'),
				'format'	=>	'FIRSTNAME LASTNAME'
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
		'WEIGHT'	=> array (
			'front'		=> array (
				'title'		=>	'Weight',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10
			)
		),
		'RANK'	=> array (
			'front'		=> array (
				'title'		=>	'Access level',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'int',
				'arraytouse'=>	'ivy_rank',
				'size'		=>	3
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'CONNECT_NAVIGATION',
		'pk'	=>	array('NAVIGATIONID'),
		'page'	=>	250
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>