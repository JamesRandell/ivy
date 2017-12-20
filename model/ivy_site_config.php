<?php

$array = array (
	'fieldSpec'	=> array (
		'SITEID'	=> array (
			'front'		=> array (
				'title'		=>	'Site ID',
				'type'		=>	'text',
				'tip'		=>	'This is the ID of your site.  It will be used as the folder name and the ID in the database entry.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'unique'	=>	'y',
				'required'	=>	'y',
			)
		),
		'SYSTEM_NAME'	=> array (
			'front'		=> array (
				'title'		=>	'Name',
				'type'		=>	'text',
				'size'		=>	50,
				'tip'		=>	'The name is used by the templates.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255,
				'required'	=>	'y',
			)
		),
		'SYSTEM_COLOR'	=> array (
			'front'		=> array (
				'title'		=>	'Colour',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	7
			)
		),
		'SYSTEM_UNIXFORMAT'	=> array (
			'front'		=> array (
				'title'		=>	'Unixformat',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26
			)
		),
		'DB_DEBUG'	=> array (
			'front'		=> array (
				'title'		=>	'Database debug',
				'type'		=>	'select',
				'tip'		=>	'Enabling this will display all SQL statements.'
			),
			'back'		=> array (
				'type'		=>	'int',
				'arraytouse'=>	'ivy_bool',
				'size'		=>	1
			)
		),
		'OUTPUT_TYPE'	=> array (
			'front'		=> array (
				'title'		=>	'Type',
				'type'		=>	'select',
				'tip'		=>	'The template engine to choose from (only the default one is available at this time).'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'outputengine',
				'required'	=>	'y',
				'size'		=>	10
			)
		),
		'OUTPUT_THEME'	=> array (
			'front'		=> array (
				'title'		=>	'Theme',
				'type'		=>	'text',
				'tip'		=>	'Choose a theme that you have installed in the "shared" directory (options are: glospol (which i will remove!!!)).'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	10
			)
		),
		'OUTPUT_CACHE'	=> array (
			'front'		=> array (
				'title'		=>	'Cache',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'outputcache',
				'size'		=>	10
			)
		),
		'OUTPUT_DEBUG'	=> array (
			'front'		=> array (
				'title'		=>	'Debug',
				'type'		=>	'select',
				'tip'		=>	'Displays debugging information about the running 
									script.'
			),
			'back'		=> array (
				'type'		=>	'int',
				'arraytouse'=>	'ivy_bool',
				'size'		=>	1
			)
		),
		'USER_STRENGTH'	=> array (
			'front'		=> array (
				'title'		=>	'User strength',
				'type'		=>	'select',
				'tip'		=>	'Enabling this will display template regions.'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'ivy_userstrength',
				'size'		=>	2
			)
		),
		'SYSTEM_ICON'	=> array (
			'front'		=> array (
				'title'		=>	'Icon',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'size'		=>	55
			)
		),
		'ARCHIVE'	=> array (
			'front'		=> array (
				'title'		=>	'Archive',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'ivy_bool',
				'required'	=>	'y',
				'size'		=>	3
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'IVY_SITE_CONFIG',
		'pk'	=>	array('SITEID'),
		'page'	=>	20,
		'auto'=>	0
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>
