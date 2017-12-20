<?php

$array = array (
	'fieldSpec'	=> array (
		'GROUPID'	=> array (
			'front'		=> array (
				'title'		=>	'GROUPID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		),
		'GROUPNAME'	=> array (
			'front'		=> array (
				'title'		=>	'Site',
				'type'		=>	'hidden',
				'size'		=>	'40'),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
				)
			),
		'GROUPDESCRIPTION'	=> array (
			'front'		=> array (
				'title'		=>	'Group name',
				'type'		=>	'text',
				'tip'		=>	'This is the unique human readable identifier for a group.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55)
			),
		'RANK'	=> array (
			'front'		=> array (
				'title'		=>	'Rank',
				'type'		=>	'text',
				'tip'		=>	'A numeric value generally used by the system to determine if 
									a user has enough clearnece to perform certain actions.  
									if you do not know what this value should be, <strong>
									leave this blank!</strong>'
			),
			'back'		=> array (
				'type'		=>	'int',
				'unique'	=>	'y',
				'required'	=>	'y',
				'size'		=>	3)
			)
		),
	'tableSpec'	=> array (
		'name'	=>	'IVY_GROUP',
		'pk'	=>	array('GROUPID'),
		'page'	=>	100,
	),
	
	'databaseSpec'	=> array (

		)
	);
?>
