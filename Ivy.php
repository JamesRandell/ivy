<?php
/**
 * SVN FILE: $Id: Ivy.php 19 2008-10-02 07:56:39Z shadowpaktu $
 *
 * Project Name : Project Description
 *
 * @package className
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 19 $
 * @lastrevision $Date: 2008-10-02 08:56:39 +0100 (Thu, 02 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-02 08:56:39 +0100 (Thu, 02 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy.php $

 */


class Ivy {}
/**
 * What site are we looking at.  Based on how the user accessed this 
 * script via a virtual directory
 */

//$site = ltrim(dirname($_SERVER['SCRIPT_NAME']), '/');
//$site = (($site == '\\') ? ltrim(rtrim($_SERVER['SCRIPT_NAME'], '/'), '/') : $site);

//$site = 'connect';

//define('SITE', $site);
unset($site);

/**
 * The full path of the orininal site location
 * DJL 05/03/18 - So sitepath can be defined in the app loader.
 */
if ( !defined ( 'SITEPATH' ) )
{
	define ( 'SITEPATH', dirname ( $_SERVER['SCRIPT_FILENAME'] ) ) ;
}

/**
 * Start the timer
 */
define('TIMER', microtime());

/**
 * define error reporting levels
 */

define('E_USER_SLOWSCRIPT', 1048);

/**
 * where are these Ivy base files?
 */
if (!defined('IVYPATH')) {
	define('IVYPATH', $_SERVER['DOCUMENT_ROOT'] . '/Ivy');
}

/**
 * Returns how much time has passed since the TIMER constant 
   was first defined, in milliseconds.
 *
 * @return	string		The value in milliseconds
 */
function timerSelect () {

	list($startUsec, $startSec) = explode(' ', TIMER);	
	list($nowUsec, $nowSec) = explode(' ',microtime());
	
	$start = (float) $startUsec + (float) $startSec;
	$now = (float) $nowUsec + (float) $nowSec;	

	$diff = $now - $start;

	$diff = round(($diff * 1000));
	
	return $diff;
}

# DJL 27/02/18
# Patch added to cope with being on linux servers
spl_autoload_register ( function ( $className ) 
{
	$directories = array 
	(
		IVYPATH . '/',
		'site/' . SITE . '/controller/',
		IVYPATH . '/templating/',
	) ;

	foreach ($directories as $directory) 
	{
		if (is_file($directory . $className . '.php')) 
		{
			include $directory . $className . '.php';
			return;
		}
	}
} ) ;

/*
function __autoload($class_name) {
	$directories = array (
		'core/',
		'site/' . SITE . '/include/application/',
		'site/' . SITE . '/controller/',
		'core/templating/',
		'core/controller/',
		'core/interface/',
	);

	foreach ($directories as $directory) {
		if (is_file($directory.$class_name . '.php')) {
			require_once $directory . $class_name . '.php';
			return;
		}
	}
}
*/

function checkFile ($file) {
	
	(array) $array = array ();	
	(array) $fileArray = explode('.', $file);
	
	$array['ext'] = end($fileArray);

	if (is_readable(SITEPATH . '/site/' . SITE . '/resource/css/' . $file)) {
		$array['path'] = SITEPATH . '/site/' . SITE . '/resource/css/' . $file;
	} else if (is_readable(SITEPATH . '/site/' . SITE . '/resource/script/' . $file)) {
		$array['path'] = SITEPATH . '/site/' . SITE . '/resource/script/' . $file;
	} else if (is_readable('shared/' . THEME . '/css/' . $file)) {
		$array['path'] = 'shared/' . THEME . '/css/' . $file;
	} else if (is_readable('shared/' . THEME . '/script/' . $file)) {
		$array['path'] = 'shared/' . THEME . '/script/' . $file;
	} else if (defined('EXTENSION') && is_readable('extension/' . EXTENSION . '/resource/css/' . $file)) {
		$array['path'] = 'extension/' . EXTENSION . '/resource/css/' . $file;
	} else if (defined('EXTENSION') && is_readable('extension/' . EXTENSION . '/resource/script/' . $file)) {
		$array['path'] = 'extension/' . EXTENSION . '/resource/script/' . $file;
	} else if (is_readable($file)) {
		$array['path'] = $file;
	} else {
		$array['ext'] = false;
		$array['path'] = false;
	}
	
	return $array;
		
}

/**
 * custom functions to auto prepend and append <pre> tags to the standard print_r/var_dump functions
 *
 * This was made is i'm sick to the back teeth of having to wrap <pre> tags around the print_r function.
 *
 * @datemodified	04/04/2017
 * @author 	James Randell <james.randell@curtisfitchglobal.com>
 */
function print_pre ($array) {
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

/**
 * custom functions to auto prepend and append <pre> tags to the standard print_r/var_dump functions
 *
 * This was made is i'm sick to the back teeth of having to wrap <pre> tags around the print_r function.
 *
 * @datemodified	04/04/2017
 * @author 	James Randell <james.randell@curtisfitchglobal.com>
 */
function print_r_pre ($array) {
	print_pre($array);
}

/**
 * custom functions to auto prepend and append <pre> tags to the standard print_r/var_dump functions
 *
 * This was made is i'm sick to the back teeth of having to wrap <pre> tags around the print_r function.
 *
 * @datemodified	04/04/2017
 * @author 	James Randell <james.randell@curtisfitchglobal.com>
 */
function var_dump_pre ($array) {
	echo '<pre>';
	var_dump($array);
	echo '</pre>';
}

$errorhandler = Ivy_Error::getInstance();
set_error_handler(array($errorhandler, 'handler'));
 
 
$ivy = new Ivy_Router();





if (timerSelect() >= 500) {
	trigger_error('Script ran for: ' . timerSelect());
}

if (memory_get_usage() > 1000000) {
	trigger_error('Excess memory usage: ' . memory_get_usage());
}
?>