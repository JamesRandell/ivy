<?php
$array = array (
	'fieldSpec'	=> array (
		'ATTRIBUTEID'	=> array (
			'front'		=> array (
				'title'		=>	'User ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'auto'		=>	'y',
				'size'		=>	10
			)
		),
		'ID'	=> array (
			'front'		=> array (
				'title'		=>	'ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
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
		'ATTRIBUTENAME'	=> array (
			'front'		=> array (
				'title'		=>	'Name',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'attributename',
				'required'	=>	'y',
				'size'		=>	55
			)
		),
		'ATTRIBUTEVALUE'	=> array (
			'front'		=> array (
				'title'		=>	'Value',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	255
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'CONNECT_ATTRIBUTE',
		'pk'	=>	array('ATTRIBUTEID'),
		'page'	=>	25
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>