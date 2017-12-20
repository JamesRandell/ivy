<?php

$array = array (
	'fieldSpec'	=> array (
		'USERGROUPID'	=> array (
			'front'		=> array (
				'title'		=>	'ID',
				'type'		=>	'hidden',
				'size'		=>	'40'
			),
			'back'		=> array (
				'size'		=>	10,
				'type'		=>	'int',
				'auto'		=>	'y',
			)
		),
		'GROUPID'	=> array (
			'front'		=> array (
				'title'		=>	'Site',
				'type'		=>	'select',
				'size'		=>	'40'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10
			),
			'join'		=> array (
				'type'		=>	'INNER',
				'table'		=>	'ivy_group',
				'pk'		=>	'GROUPID',
				'fields'	=>	array('GROUPNAME','GROUPDESCRIPTION','RANK')
			),
			'options'		=> array (
				'table'		=>	'ivy_group',
				'key'		=>	'GROUPID',
				'value'		=>	'GROUPDESCRIPTION',
			),
			'replace'		=> array (
				'table'		=>	'ivy_group',
				'key'		=>	'GROUPID',
				'fields'	=>	array('GROUPNAME'),
				'format'	=>	'GROUPNAME'
			),
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'Collar',
				'type'		=>	'text',
				'class'		=>	'autocomplete',
				'tip'		=>	'You can enter a name or a collar number.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			),
			'replace'		=> array (
				'table'		=>	'ivy_user_profile',
				'key'		=>	'COLLAR',
				'fields'	=>	array('FIRSTNAME','LASTNAME'),
				'format'	=>	'FIRSTNAME LASTNAME (COLLAR)'
			),
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Date created',
				'type'		=>	'hidden',
				'size'		=>	'80'
			),
			'back'		=> array (
				
				'default'	=>	'date',
				'type'		=>	'unix',				
				'size'		=> 10
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_USER_GROUP',
		'pk'	=>	array('USERGROUPID'),
	),
	
	'databaseSpec'	=> array (

	)
);
?>
