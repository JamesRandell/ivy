<?php

$array = array (
	'fieldSpec'	=> array (
		'ERRORID'	=> array (
			'front'		=> array (
				'title'		=>	'Error ID',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'Collar',
				'type'		=>	'text',
			),
			'back'		=> array (
				'default'	=>	'collar',
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Date generated',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'default'	=>	'date',
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'NO'	=> array (
			'front'		=> array (
				'title'		=>	'Number',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'required'	=>	'y',
			)
		),
		'STR'	=> array (
			'front'		=> array (
				'title'		=>	'String',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'truncate'	=>	1000,
				'size'		=>	1000,
				'required'	=>	'y',
			)
		),
		'FILENAME'	=> array (
			'front'		=> array (
				'title'		=>	'File',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'URL'	=> array (
			'front'		=> array (
				'title'		=>	'URL',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
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
				'truncate'	=>	55,
				'size'		=>	55
			)
		),
		'LINE'	=> array (
			'front'		=> array (
				'title'		=>	'Line',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'int',
				'required'	=>	'y',
				'size'		=>	10
			)
		),
		'TYPE'	=> array (
			'front'		=> array (
				'title'		=>	'Type',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_ERROR',
		'pk'	=>	array('ERRORID'),
		'page'	=>	20,
	),
	
	'databaseSpec'	=> array (

	)
);
?>
