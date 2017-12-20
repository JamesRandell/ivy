<?php
$array = array (
	'fieldSpec'	=> array (
		'COLLAR'	=> array (
			'front'		=> array (
				'title'		=>	'User ID',
				'type'		=>	'hidden'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'TITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Title',
				'type'		=>	'select'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'title',
				'size'		=>	55
			)
		),
		'FIRSTNAME'	=> array (
			'front'		=> array (
				'title'		=>	'First name',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'LASTNAME'	=> array (
			'front'		=> array (
				'title'		=>	'Last name',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'JOBTITLE'	=> array (
			'front'		=> array (
				'title'		=>	'Job Title',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	500
			)
		),
		'DATELOGIN'	=> array (
			'front'		=> array (
				'title'		=>	'Date login',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'DATEMODIFIED'	=> array (
			'front'		=> array (
				'title'		=>	'Date modified',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'unix',
				'size'		=>	10
			)
		),
		'LOCATION1'	=> array (
			'front'		=> array (
				'title'		=>	'Work location',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'LOCATION2'	=> array (
			'front'		=> array (
				'title'		=>	'Floor (if possible)',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'LOCATION3'	=> array (
			'front'		=> array (
				'title'		=>	'Pod',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'IP'	=> array (
			'front'		=> array (
				'title'		=>	'IP',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26
			)
		),
		'ASSETNO'	=> array (
			'front'		=> array (
				'title'		=>	'Workstation',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	26
			)
		),
		'PHONE'	=> array (
			'front'		=> array (
				'title'		=>	'My Phone No.',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	55
			)
		),
		'EMAIL'	=> array (
			'front'		=> array (
				'title'		=>	'Email',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'DIVISION'	=> array (
			'front'		=> array (
				'title'		=>	'Directorate',
				'type'		=>	'text',
				'size'		=>	40,
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'DEPARTMENT'	=> array (
			'front'		=> array (
				'title'		=>	'Organisation',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'COMPANY'	=> array (
			'front'		=> array (
				'title'		=>	'Company',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			)
		),
		'LINEMANAGER'	=> array (
			'front'		=> array (
				'title'		=>	'Line Manager',
				'type'		=>	'text',
				'class'		=>	'autocomplete'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			),
			'replace'	=> array (
				'table'		=>	'AD_USER',
				'key'		=>	'COLLAR',
				'fields'	=>	array('FIRSTNAME','LASTNAME'),
				'format'	=>	'FIRSTNAME LASTNAME'
			)
		),
		'ASSOCIATE'	=> array (
			'front'		=> array (
				'title'		=>	'Associate',
				'type'		=>	'text',
				'class'		=>	'autocomplete'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			),
			'replace'	=> array (
				'table'		=>	'AD_USER',
				'key'		=>	'COLLAR',
				'fields'	=>	array('FIRSTNAME','LASTNAME'),
				'format'	=>	'FIRSTNAME LASTNAME'
			)
		),
		'PA'	=> array (
			'front'		=> array (
				'title'		=>	'PA',
				'type'		=>	'text',
				'class'		=>	'autocomplete'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	255
			),
			'replace'	=> array (
				'table'		=>	'AD_USER',
				'key'		=>	'COLLAR',
				'fields'	=>	array('FIRSTNAME','LASTNAME'),
				'format'	=>	'FIRSTNAME LASTNAME'
			)
		),
		'PHOTO'	=> array (
			'front'		=> array (
				'title'		=>	'Photo',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'arraytouse'=>	'yesno',
				'size'		=>	10
			)
		),
		'ESRID'	=> array (
			'front'		=> array (
				'title'		=>	'Employee No',
				'type'		=>	'text'
			),
			'back'		=> array (
				'type'		=>	'var',
				'size'		=>	10
			)
		),
	),
	'tableSpec'	=> array (
		'name'	=>	'USER_DETAIL',
		'pk'	=>	array('COLLAR'),
		'page'	=>	25
		
	),
	
	'databaseSpec'	=> array (

	)
);
?>