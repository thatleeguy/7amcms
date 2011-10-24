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
 * The Key Model
 *
 * @subpackage	Models
 * @category	Key
 */
class Key_m extends Breakfast_Model
{
	/**
	 * @var string	The name of the settings table
	 */
	protected $table = 'keys';

	/**
	 * @var bool	Tells the model to skip auto validation
	 */
	protected $skip_validation = TRUE;
	
	public function update_keys($keys, $notes)
	{
		$this->db->trans_begin();

		foreach ($keys as $id => $key)
		{
			// Missing key
			if ( ! $key)
			{
				$this->db->delete($this->table, array('id' => $id));
			}
			
			$this->db->where('id', $id)->update($this->table, array(
				'key' => $key,
				'note' => $notes[$id], 
			));
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$this->db->trans_commit();
		return TRUE;
	}

	public function insert_keys($keys, $notes)
	{
		$this->db->trans_begin();

		foreach ($keys as $id => $key)
		{
			if ($key)
			{
				$this->db->insert($this->table, array(
					'key' => $key,
					'note' => $notes[$id],
					'date_created' => now(),
				));
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

/* End of file: key_m.php */