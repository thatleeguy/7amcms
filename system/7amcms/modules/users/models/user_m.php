<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright	Copyright (c) 2010, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * The User Model
 *
 * @subpackage	Models
 * @category	Users
 */
class User_m extends Breakfast_Model
{
	/**
	 * @var	string	The name of the clients table
	 */
	protected $table = 'users';

	protected $validate = array(
		array(
			'field'	  => 'username',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'email',
			'rules'	  => 'required|valid_email'
		),
		array(
			'field'	  => 'password',
			'rules'	  => 'required'
		),
	);
	
	public function get_last_visited_version($user_id) {
	    $buffer = $this->db->select('last_visited_version')->where('user_id', $user_id)->get('meta')->row_array();
	    return $buffer['last_visited_version'];
	}
}

/* End of file: user_m.php */