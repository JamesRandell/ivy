<?php

$array = array (
	'fieldSpec'	=> array (
		'CONTENTID'	=> array (
			'front'		=> array (
				'title'		=>	'CMS ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'int',
				'size'		=>	10,
				'auto'		=>	'y',
			)
		),
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'Author',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'varchar',
				'size'		=>	55
			)
		),
		'DATECREATED'	=> array (
			'front'		=> array (
				'title'		=>	'Date created',
				'type'		=>	'hidden',
			),
			'back'		=> array (				
				'default'	=>	'date',
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'TITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'text',
				'size'		=>	50,
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	255
			)
		),
		'CONTENT'	=> array (
			'front'		=> array (
				'title'		=>	'Content',
				'type'		=>	'textarea',
				'cols'		=>	20,
				'rows'		=>	16,
				'class'		=>	'tinymce',
			),
			'back'		=> array (
				'type'		=>	'clob',
			)
		),
		'METATITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Meta title',
				'type'		=>	'text',
				'size'		=>	50,
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'METACONTENT'	=> array (
			'front'		=> array (
				'title'		=>	'Meta content',
				'type'		=>	'textarea',
				'cols'		=>	50,
				'rows'		=>	4,
			),
			'back'		=> array (
				'type'		=>	'clob'
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_CONTENT',
		'pk'	=>	array('CONTENTID'),
	),
	
	'databaseSpec'	=> array (

	)
);
?>

