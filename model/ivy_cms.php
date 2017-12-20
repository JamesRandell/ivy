<?php

$array = array (
	'fieldSpec'	=> array (
		'CMSID'	=> array (
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
		'SITEID'	=> array (
			'front'		=> array (
				'title'		=>	'Site ID',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
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
		'CONTROLLER'	=> array (
			'front'		=> array (
				'title'		=>	'Controller',
				'type'		=>	'hidden',
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	26
			),
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
			),
		),
		'URL'	=> array (
			'front'		=> array (
				'title'		=>	'Other URL parts',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_CMS',
		'pk'	=>	array('CMSID'),
	),
	
	'databaseSpec'	=> array (

	)
);
?>

