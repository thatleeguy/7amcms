<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


if(isset($_SERVER['HTTP_HOST']))
{
	$base_url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';
	$base_url .= '://'. $_SERVER['HTTP_HOST'].'/';
        
        $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : (basename($_SERVER['SCRIPT_NAME'])));      
        $path = str_replace(array($path, 'index.php'), '', $_SERVER['SCRIPT_NAME']);
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1, strlen($path) - 1);
        }
	$base_url .= $path;
	
	if (isset($_SERVER['SCRIPT_URI']) and !empty($_SERVER['SCRIPT_URI'])) {
	    $base_url = $_SERVER['SCRIPT_URI'];
	    
	    if (!empty($_SERVER['PATH_INFO'])) {
		# Remove path info from base url.
		if (substr($base_url, -strlen($_SERVER['PATH_INFO'])) == $_SERVER['PATH_INFO']) {
		    $base_url = substr($base_url, 0, -strlen($_SERVER['PATH_INFO']));
		}
	    } elseif (!empty($_SERVER['REQUEST_URI'])) {
		$buffer = (substr($_SERVER['REQUEST_URI'], -1, 1) == '/') ? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'].'/';
		$base_url = (substr($base_url, -1, 1) == '/') ? $base_url : $base_url.'/';
		if (substr($base_url, -strlen($buffer)) == $buffer) {
		    $base_url = substr($base_url, 0, -strlen($buffer));
		}
	    }
	}
	
	# Add the forward slash, always.
	$base_url = (substr($base_url, -1, 1) == '/') ? $base_url : $base_url.'/';
}

else
{
	$base_url = 'http://localhost/';
}

// Define these values to be used later on
define('BASE_URL', (substr($base_url, -1) != '/') ? $base_url.'/' : $base_url);
define('COPYRIGHT_YEAR', 2011);

// We dont need these variables any more
unset($base_url);

/* End of file constants.php */
/* Location: ./application/config/constants.php */