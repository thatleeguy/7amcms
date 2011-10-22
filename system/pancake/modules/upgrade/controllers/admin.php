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
 * @since		Version 3.1.0
 */
// ------------------------------------------------------------------------

/**
 * The admin controller for the upgrade system
 *
 * @subpackage	Controllers
 * @category	Upgrade
 */
class Admin extends Admin_Controller {

    public function __construct() {
	parent::__construct();
    }

    function no_internet_access() {
	$this->template->set_layout('login');
	$this->template->title(__('update:nointernetaccess'));
	$this->template->build('no_internet_access', array('hide_header' => true));
    }
    
    function update() {
	$this->load->model('upgrade/update_system_m', 'update');
	if ($this->update->write or $this->update->ftp) {
	    $this->update->update_pancake(Settings::get('latest_version'));
	} else {
	    redirect('admin/settings#update');
	}
    }
    
    function update_if_no_conflicts() {
	$this->load->model('upgrade/update_system_m', 'update');
	if ($this->update->write or $this->update->ftp) {
	    if (count($this->update->check_for_conflicts()) == 0) {
		# There are no conflicts, upgrade.
		$this->update->update_pancake(Settings::get('latest_version'));
	    }
	} else {
	    redirect('admin/settings#update');
	}
    }
    
}