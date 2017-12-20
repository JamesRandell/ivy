<?php

$array = array (
	'fieldSpec'	=> array (
		'system_unixformat'	=> array (
			'front'		=> array (
				'title'		=>	'Unif format',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'default'	=>	'jS F Y, g:ia'
			)
		),
		'system_name'	=> array (
			'front'		=> array (
				'title'		=>	'Name',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255,
				'default'	=>	'IVY Site',
				'required'	=>	'y',
			)
		),
		'system_color'	=> array (
			'front'		=> array (
				'title'		=>	'Colour',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	7,
				'default'	=>	'#333'
			)
		),
		
		'db_debug'	=> array (
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
		'db_type'	=> array (
			'front'		=> array (
				'title'		=>	'Database type',
				'type'		=>	'select',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26,
				'arraytouse'=>	'ivy_db_type',
				'required'	=>	'y',
			)
		),
		'db_server'	=> array (
			'front'		=> array (
				'title'		=>	'Database server',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'required'	=>	'y',
			)
		),
		'db_database'	=> array (
			'front'		=> array (
				'title'		=>	'Database name',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
			)
		),
		'db_username'	=> array (
			'front'		=> array (
				'title'		=>	'Database username',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'required'	=>	'y',
			)
		),
		'db_password'	=> array (
			'front'		=> array (
				'title'		=>	'Database password',
				'type'		=>	'text',
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55,
				'required'	=>	'y',
			)
		),
		'output_type'	=> array (
			'front'		=> array (
				'title'		=>	'Output type',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'outputengine',
				'required'	=>	'y',
				'size'		=>	10
			)
		),
		'output_theme'	=> array (
			'front'		=> array (
				'title'		=>	'Theme',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'required'	=>	'y',
				'arraytouse'=>	'ivy_output_theme',
				'size'		=>	10
			)
		),
		'output_cache'	=> array (
			'front'		=> array (
				'title'		=>	'Output cache',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'outputcache',
				'size'		=>	10
			)
		),
		'output_debug'	=> array (
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
	),
	'tableSpec'	=> array (	
	),
	
	'databaseSpec'	=> array (
		'type'			=>	'ini',
		'server'		=>	'core/config/config.ini',
	)
);
?>
