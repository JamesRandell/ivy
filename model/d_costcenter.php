<?php
$array = array (
	'fieldSpec'	=> array (
		'COSTCENTERID'	=> array (
			'front'		=> array (
				'title'		=>	'Costcenter ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10
			)
		),
		'TITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'DIVISION'	=> array (
			'front'		=> array (
				'title'		=>	'Division',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		
	),
	'tableSpec'	=> array (
		'name'	=>	'CONNECT_D_COSTCENTER',
		'pk'	=>	array('COSTCENTERID'),
		'page'	=>	250
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>