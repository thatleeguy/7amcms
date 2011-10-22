<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright		Copyright (c) 2011, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 3.1
 */
// ------------------------------------------------------------------------

/**
 * The Pancake Update System
 *
 * @subpackage	Models
 * @category	Upgrade
 */
class Update_system_m extends Pancake_Model {

    public $write = false;
    public $ftp = false;
    public $ftp_conn = false;
    public $error;

    /**
     * The construct.
     * 
     * Verifies if Pancake can write to itself or FTP to itself.
     */
    function __construct() {
	
	include_once APPPATH . 'libraries/HTTP_Request.php';
	
	if (function_exists('getmyuid') && function_exists('fileowner')) {
	    $test_file = FCPATH . 'uploads/test-' . time();
	    $test = @fopen($test_file, 'w');
	    if ($test) {
		$this->write = (getmyuid() == @fileowner($test_file));
		@fclose($test);
		@unlink($test_file);
	    } else {
		$this->write = false;
	    }
	} else {
	    $this->write = false;
	}
	
	if (!$this->write) {
	    $user = Settings::get('ftp_user');
	    $this->ftp = !empty($user);
	}
	
    }

    function get_error() {
	return __('update:' . $this->error);
    }

    function pancake_updated() {
	$http = new HTTP_Request();
	$data = array(
	    'LICENSE_KEY' => Settings::get('license_key'),
	    'CHECK_LATEST_VERSION_URL' => site_url('check_latest_version'),
	    'HAS_AUTO_UPDATE' => Settings::get('auto_update'),
	    'CURRENT_VERSION' => Settings::get('version'),
	    'LATEST_DOWNLOADED_VERSION' => Settings::get ('latest_version'),
	    'USING_FTP' => $this->ftp ? 'YES' : 'NO'
	);
	try {
	    return (trim($http->request('http://manage.pancakeapp.com/download/pancake_updated/'.time(), 'POST', $data)) == 'YAY');
	} catch (Exception $e) {
	    deal_with_no_internet();
	    return false;
	}
    }

    /**
     * Checks if a given FTP configuration works with Pancake.
     * 
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param integer $port
     * @param string $path
     * @param boolean $passive
     * @return boolean 
     */
    function test_ftp($host, $user, $pass, $port, $path, $passive) {
	$passive = (bool) $passive;
	$port = (int) $port;
	$path = (substr($path, strlen($path) - 1, 1) == '/') ? $path : $path . '/';

	$connection = @ftp_connect($host, $port);

	if (!$connection) {
	    $this->error = 'ftp_conn';
	    return false;
	} else {

	    if (!@ftp_login($connection, $user, $pass)) {
		$this->error = 'ftp_login';
		return false;
	    }

	    ftp_pasv($connection, $passive);

	    if (!ftp_chdir($connection, $path)) {
		$this->error = 'ftp_chdir';
		return false;
	    }
	}

	$tmpNam = tempnam(sys_get_temp_dir(), 'test');

	if (@ftp_get($connection, $tmpNam, 'index.php', FTP_ASCII)) {
	    if (stristr(file_get_contents($tmpNam), "define('ENVIRONMENT', ") !== false) {
		$uploaded = ftp_put($connection, 'uploads/test.txt', $tmpNam, FTP_BINARY);
		if ($uploaded) {
		    @ftp_delete($connection, 'uploads/test.txt');
		    return true;
		} else {
		    $this->error = 'ftp_no_uploads';
		    return false;
		}
	    } else {
		$this->error = 'ftp_indexwrong';
		return false;
	    }
	    fclose($tmpFile);
	} else {
	    # Couldn't get the file. I assume it's because the file didn't exist.
	    $this->error = 'ftp_indexnotfound';
	    return false;
	}

	return true;
    }
    
    /**
     * Finish downloading a version's files. Hangs the script till the version is done downloading.
     * If the version is already downloading asynchronously, it waits for it to finish.
     * 
     * @param string $version
     * @return boolean 
     */
    function finish_downloading($version, $only_this_version = false) {
	if (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $version)) {
	    $this->download_version($version, $only_this_version);
	}

	$slept = 0;
	while (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $version . '/finished.txt')) {
	    if ($slept < 15) { # Timeout is 15 seconds. Sorry, that's more than enough time.
		# It's still downloading the new version, let's wait.
		sleep(1);
		$slept++;
	    } else {
		show_error('A problem occurred while trying to download the latest version of Pancake. Please send an email to support@pancakeapp.com immediately, letting us know.');
	    }
	}

	return true;
    }

    /**
     * Gets the list of changes between $to and $from, in processed Markdown HTML.
     * 
     * @param string $to
     * @param string $from
     * @return string
     */
    function get_processed_changelog($to, $from = null) {
	
	if ($to == $from) {
	    return '';
	}

	if ($from === null) {
	    $from = Settings::get('version');
	}

	$this->finish_downloading($to, true);

	$changelog = file_get_contents(FCPATH . 'uploads/pancake-update-system/' . $to . '/processed_changelog.txt');
	$changelog = explode('<h3>v' . $from . '</h3>', $changelog);
	$changelog = explode('<h3>v' . $to . '</h3>', $changelog[0]);
	if (isset($changelog[1])) {
	    $changelog = trim($changelog[1]);
	} else {
	    return '';
	}
	
	if (stristr($changelog, '<h3>v2.1.0</h3>')) {
	    # Version is in the past and it got the changelog bug, return empty changelog.
	    return '';
	}
	
	return $changelog;
    }
    
    function get_versions_between($to, $from = null) {
	$return = array();
	$changelog = $this->get_processed_changelog($to, $from);
	$changelog = explode('</h3>', $changelog);
	foreach ($changelog as $version) {
	    $version = explode('<h3>v', $version);
	    if (count($version) > 1) {
		$return[] = $version[1];
	    }
	}
	
	sort($return);
	return $return;
    }

    /**
     * Updates Pancake to the latest version.
     * 
     * If Pancake cannot write to itself via PHP, it will try to do so via FTP.
     * If it can't do it with either, it will return false.
     * 
     * @return boolean
     */
    function update_pancake($version, $refresh = true) {

	$update_first = $this->get_versions_between($version);
	foreach ($update_first as $version_to_update_to) {
	    # Updating from 3.1.6 to 3.1.10, $update_first is 3.1.7, 3.1.8, 3.1.9.
	    # Upgrades from 3.1.6 to 3.1.7 first, then from 3.1.7 to 3.1.8, then from 3.1.8 to 3.1.9, then it continues upgrading to 3.1.10.
	    $this->update_pancake($version_to_update_to, false);
	}
	
	$this->finish_downloading($version);

	if ($this->write or $this->ftp) {
	    $changed_files = file(FCPATH . 'uploads/pancake-update-system/' . $version . '/changed_files.txt');

	    foreach ($changed_files as $file) {
		$file = trim($file);
		if (!empty($file)) {
		    if (substr($file, 0, 5) == '[Modi') {
			# Modified.
			$file = substr($file, 11);
			$this->set_file_contents(FCPATH . $file, file_get_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/extracted-zip/Pancake-V' . str_ireplace('.', '_', $version) . 'U/pancake/' . $file));
		    } elseif (substr($file, 0, 5) == '[Adde') {
			# Added.
			$file = substr($file, 8);
			$this->set_file_contents(FCPATH . $file, file_get_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/extracted-zip/Pancake-V' . str_ireplace('.', '_', $version) . 'U/pancake/' . $file));
		    } else {
			# Deleted.
			$file = substr($file, 10);
			$this->delete(FCPATH . $file);
		    }
		}
	    }
	    
	    # Force migrations to run.
	    get_url_contents(site_url('admin'), false);
	    
	    if ($refresh) {
		# Force a refresh after this update.
		redirect('admin');
	    }
	} else {
	    $this->error = 'update_no_perms';
	    return false;
	}
    }

    /**
     * Checks to see which files are different.
     * 
     * @param string $version 
     */
    function check_for_conflicts($version) {
	if (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $version)) {
	    $this->download_version($version);
	}

	if (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $version . '/hashes_' . str_ireplace('.', '_', Settings::get('version')) . '.txt')) {
	    # hashes_current_version.txt does not exist, it needs to be retrieved from the server!
	    $http = new HTTP_Request();
	    try {
		$hashes_content = $http->request('http://manage.pancakeapp.com/changes/' . Settings::get('version') . '/HASHES?' . time());
	    } catch (Exception $e) {
		deal_with_no_internet();
		return array();
	    }
	    file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/hashes_' . str_ireplace('.', '_', Settings::get('version')) . '.txt', $hashes_content);
	    unset($http);
	    unset($hashes_content);
	}

	$changed_files = file(FCPATH . 'uploads/pancake-update-system/' . $version . '/changed_files.txt');
	$hash_buffer = file(FCPATH . 'uploads/pancake-update-system/' . $version . '/hashes_' . str_ireplace('.', '_', Settings::get('version')) . '.txt');

	$hashes = array();

	$conflicted = array();

	foreach ($hash_buffer as $hash) {
	    $hash = trim($hash);
	    if (!empty($hash)) {
		$hash = explode(' :.: ', $hash);
		$hashes[$hash[0]] = $hash[1];
	    }
	}

	foreach ($changed_files as $file) {
	    $file = trim($file);
	    if (!empty($file)) {
		if (substr($file, 0, 5) == '[Modi') {
		    # Modified.
		    $file = substr($file, 11);
		} elseif (substr($file, 0, 5) == '[Adde') {
		    # Added.
		    $file = substr($file, 8);
		} else {
		    # Deleted.
		    $file = substr($file, 10);
		}

		if (substr($file, 0, 8) == 'pancake/') {
		    $file = substr($file, 8);
		} elseif (stristr($file, '/') !== false or $file == '.htaccess' or $file == 'index.php' or $file == 'example.htaccess') {
		    # It's one of the deleted files that didn't get pancake/ added to it.
		    # Doubt it'll ever be .htaccess, example.htaccess or index.php, but who knows. Better to be on the safe side.
		} else {
		    # Ignore.
		    continue;
		}

		if (isset($hashes[$file])) {
		    # Exists in current version, let's see if the file STILL exists.
		    if (file_exists(FCPATH . $file)) {
			if (md5_file(FCPATH . $file) != $hashes[$file]) {
			    # File's MD5 hash is different from the original current version's hash. There IS a conflict.
			    $conflicted[$file] = 'M';
			}
		    } else {
			# File was deleted, so there IS a conflict.
			# Though I'm not sure why people would delete Pancake files.
			# But we've got to be on the safe side!
			$conflicted[$file] = 'D';
		    }
		}
	    }
	}

	return $conflicted;
    }

    /**
     * Downloads the Pancake ZIP, CHANGELOG and HASHES for $version to the uploads folder.
     * 
     */
    function download_version($version, $only_this_version = false) {

	set_time_limit(0);

	if (!file_exists(FCPATH . 'uploads/pancake-update-system')) {
	    mkdir(FCPATH . 'uploads/pancake-update-system');
	}

	if (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $version)) {
	    
	    if (!$only_this_version) {
		$download_first = $this->get_versions_between($version);
		foreach ($download_first as $download) {
		    # Downloads every version between Settings::get('version') and $version.
		    $this->finish_downloading($download, false);
		}
	    }
	    
	    mkdir(FCPATH . 'uploads/pancake-update-system/' . $version);

	    $http = new HTTP_Request();
	    try {
		$hashes_content = $http->request('http://manage.pancakeapp.com/changes/' . Settings::get('version') . '/HASHES?' . time());
	    } catch (Exception $e) {
		# If this is being loaded manually (which it never should be), redirect.
		# Even if it's not loaded manually, it doesn't matter, because the result of this page is ignored.
		rmdir(FCPATH . 'uploads/pancake-update-system/' . $version);
		redirect('no_internet_access');
	    }
	    $changelog_content = $http->request('http://manage.pancakeapp.com/CHANGELOG?' . time());
	    $changed_files_content = $http->request('http://manage.pancakeapp.com/changes/' . $version . '/CHANGED_FILES?' . time());
	    $processed_changelog_content = $http->request('http://manage.pancakeapp.com/PROCESSED_CHANGELOG?' . time());

	    if (stristr($changelog_content, '### v' . $version) !== false) {
		$zip_content = base64_decode($http->request('http://manage.pancakeapp.com/download/' . Settings::get('license_key') . '/' . $version . 'U/base64/' . time()));

		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/update.zip', $zip_content);
		
		# Extract the ZIP now.
		
		if (!file_exists(APPPATH.'libraries/Unzip.php')) {
		    # This is a pre-unzip version of Pancake that NEEDS this so it can upgrade safely.
		    $unzip = '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';
		    $unzip = base64_decode($unzip);
		    $unzip = gzinflate($unzip);
		    file_put_contents(APPPATH.'libraries/Unzip.php', $unzip);
		}
		
		$this->load->library('unzip');
		mkdir(FCPATH . 'uploads/pancake-update-system/' . $version . '/extracted-zip');
		$files = $this->unzip->extract(FCPATH . 'uploads/pancake-update-system/' . $version . '/update.zip', FCPATH . 'uploads/pancake-update-system/' . $version . '/extracted-zip/');
		
		if ($files == array() or $files === false) {
		    # There was a problem with the ZIP. Delete everything. Go back. Back. Back.
		    unlink(FCPATH . 'uploads/pancake-update-system/' . $version . '/update.zip');
		    rmdir(FCPATH . 'uploads/pancake-update-system/' . $version);
		    show_error('A problem occurred while trying to extract the latest version of Pancake. Please send an email to support@pancakeapp.com immediately, letting us know.');
		}
		
		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/changed_files.txt', $changed_files_content);
		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/hashes_' . str_ireplace('.', '_', Settings::get('version')) . '.txt', $hashes_content);
		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/changelog.txt', $changelog_content);
		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/processed_changelog.txt', $processed_changelog_content);
		
		# Tell manage.pancakeapp.com that we downloaded a new version:
		$this->pancake_updated();

		file_put_contents(FCPATH . 'uploads/pancake-update-system/' . $version . '/finished.txt', 'OKAY');

		# Okay, it's been downloaded. Now, does the user want us to auto-update?
		if (Settings::get('auto_update')) {
		    if ($this->write or $this->ftp) {
			if (count($this->check_for_conflicts()) == 0) {
			    # There are no conflicts, upgrade.
			    $this->update_pancake($version);
			}
		    }
		    return true;
		}

		return true;
	    } else {
		# Version does not exist!
		rmdir(FCPATH . 'uploads/pancake-update-system/' . $version);
		return false;
	    }
	} else {
	    return true;
	}
    }

    /**
     * Fetches the latest version IFF more than 12 hours have passed since the last fetch.
     * 
     * Downloads the latest version and caches it if it finds it.
     * The latest version downloader will create a notification for the new update, or execute
     * the update instantly, depending on the settings.
     */
    function get_latest_version($force = false) {
	$next_fetch = strtotime('+12 hours', Settings::get('latest_version_fetch'));
	if ($next_fetch < time() or $force) {
	    $http = new HTTP_Request();
	    try {
		$latest = $http->request('http://manage.pancakeapp.com/VERSION?' . time());
		Settings::set('latest_version_fetch', time());
		Settings::set('latest_version', trim($latest));
		if (!file_exists(FCPATH . 'uploads/pancake-update-system/' . $latest)) {
		    $this->request_async(site_url('download_version/' . $latest), array());
		}
		return $latest;
	    } catch (Exception $e) {
		deal_with_no_internet();
	    }
	}
    }

    function request_async($url, $params) {
	$post_params = array();
	foreach ($params as $key => &$val) {
	    if (is_array($val))
		$val = implode(',', $val);
	    $post_params[] = $key . '=' . urlencode($val);
	}
	$post_string = implode('&', $post_params);

	$parts = parse_url($url);

	$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

	$out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
	$out.= "Host: " . $parts['host'] . "\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: " . strlen($post_string) . "\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if (isset($post_string))
	    $out.= $post_string;

	fwrite($fp, $out);
	fclose($fp);
    }

    /**
     * Creates a file with $data if it doesn't exist, or updates it with $data if it exists.
     * 
     * If Pancake cannot write to itself via PHP, it will try to do so via FTP.
     * If it can't do it with either, it will return false.
     * 
     * $filename is ABSOLUTE, and starts with FCPATH.
     * 
     * @param string $filename
     * @param string $data
     * @return boolean 
     */
    function set_file_contents($filename, $data) {
	if ($this->write) {
	    
	    $dir = dirname($filename);
	    $dir = str_ireplace(FCPATH, '', $dir); 
	    $dir = explode("/", $dir);
	    $path = "";

	    for ($i = 0; $i < count($dir); $i++) {
		$path.= $dir[$i] . '/';

		if ($path != '/' and !file_exists(FCPATH.$path)) {
		    if (!@mkdir(FCPATH.$path)) {
			show_error('A problem occurred while trying to create a folder when updating Pancake. (Extra error information: '.FCPATH.' - '.$filename.' - '.$path.') Please send an email to support@pancakeapp.com immediately, letting us know.');
		    }
		}

	    }
	    
	    file_put_contents($filename, $data);
	} elseif ($this->ftp) {
	    $filename = str_ireplace(FCPATH, '', $filename);
	    $connection = $this->getFtpConnection();

	    # Create the folder where the file is in, if it does not exist. Recursive.
	    $dir = explode("/", dirname($filename));
	    $path = "";

	    for ($i = 0; $i < count($dir); $i++) {
		$path.= $dir[$i] . '/';

		$origin = ftp_pwd($connection);

		if (!@ftp_chdir($connection, $path)) {
		    if (!@ftp_mkdir($connection, $path)) {
			return false;
		    }
		}

		ftp_chdir($connection, $origin);
	    }
	    $tmpNam = tempnam(sys_get_temp_dir(), 'test');
	    file_put_contents($tmpNam, $data);
	    return @ftp_put($connection, $filename, $tmpNam, FTP_BINARY);
	} else {
	    return false;
	}
    }

    /**
     * Starts an FTP connection if necessary.
     * If an FTP connection was already established, it returns it.
     * Called whenever setting file contents or deleting files.
     */
    function getFtpConnection() {

	$host = Settings::get('ftp_host');
	$path = Settings::get('ftp_path');
	$user = Settings::get('ftp_user');
	$pass = Settings::get('ftp_pass');
	$port = Settings::get('ftp_port');
	$passive = Settings::get('ftp_pasv');

	if (!($this->ftp_conn)) {

	    $port = (int) $port;
	    $path = (substr($path, strlen($path) - 1, 1) == '/') ? $path : $path . '/';

	    $connection = @ftp_connect($host, $port);

	    if (!$connection) {
		return false;
	    } else {

		if (!@ftp_login($connection, $user, $pass)) {
		    return false;
		}

		ftp_pasv($connection, $passive);

		if (!ftp_chdir($connection, $path)) {
		    return false;
		}
	    }

	    $this->ftp_conn = $connection;

	    return $this->ftp_conn;
	} else {
	    return $this->ftp_conn;
	}
    }

    function delete($filename) {
	if ($this->write) {
	    return @unlink($filename);
	} elseif ($this->ftp) {
	    $filename = str_ireplace(FCPATH, '', $filename);
	    @ftp_delete($this->getFtpConnection(), $filename);
	} else {
	    return false;
	}
    }

}