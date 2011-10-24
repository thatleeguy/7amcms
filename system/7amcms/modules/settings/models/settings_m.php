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
 * The Settings Model
 *
 * @subpackage	Models
 * @category	Settings
 */
class Settings_m extends Breakfast_Model
{
	/**
	 * @var string	The name of the settings table
	 */
	protected $table = 'settings';

	/**
	 * @var string	The primary key
	 */
	protected $primary_key = 'slug';

	/**
	 * @var bool	Tells the model to skip auto validation
	 */
	protected $skip_validation = TRUE;

	public function update_settings($settings)
	{
		$this->db->trans_begin();

		foreach ($settings as $slug => $value)
		{
			// This ensures we are only updating what has changed
			if (PAN::setting($slug) != $value)
			{
				$this->db->where('slug', $slug)->update($this->table, array('value' => $value));
			}
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$this->db->trans_commit();
		return TRUE;
	}

}

/* End of file: settings_m.php */
