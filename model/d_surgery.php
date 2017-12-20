<?php
$array = array (
	'fieldSpec'	=> array (
		'SURGERYID'	=> array (
			'front'		=> array (
				'title'		=>	'Surgery ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
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
				'size'		=>	55
			)
		),
		'CONTENT'	=> array (
			'front'		=> array (
				'title'		=>	'Address',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'POSTCODE'	=> array (
			'front'		=> array (
				'title'		=>	'Postcodet',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10
			)
		),
		'IPSTART'	=> array (
			'front'		=> array (
				'title'		=>	'IP Start',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	15
			)
		),
		'IPEND'	=> array (
			'front'		=> array (
				'title'		=>	'IP End',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	15
			)
		),
		'PCTCODE'	=> array (
			'front'		=> array (
				'title'		=>	'PCT Code',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	3
			)
		),
		'SNN2'	=> array (
			'front'		=> array (
				'title'		=>	'N2 SN',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	6
			)
		)
	),
	'tableSpec'	=> array (
		'name'	=>	'CONNECT_D_SURGERY',
		'pk'	=>	array('SURGERYID'),
		'page'	=>	1000
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>