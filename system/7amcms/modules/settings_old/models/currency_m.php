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
class Currency_m extends Breakfast_model
{
	/**
	 * @var string	The name of the settings table
	 */
	protected $table = 'currencies';

	public function update_currencies($names, $codes, $rates)
	{
		$this->db->trans_begin();

		foreach ($names as $id => $name)
		{
			// Missing nme and code, so just delete
			if ( ! $name and ! $codes[$id])
			{
				$this->db->delete($this->table, array('id' => $id));
			}

			// This ensures we are only updating what has changed
			else if (Settings::currency($id) != $rates[$id])
			{
				$data = array('name' => $name, 'code' => $codes[$id], 'rate' => $rates[$id]);
				$this->db->where('id', $id)->update($this->table, $data);
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

	public function insert_currencies($names, $codes, $rates)
	{
		$this->db->trans_begin();

		foreach ($names as $id => $name)
		{
			if ($name and $codes[$id])
			{
				$data = array('name' => $name, 'code' => $codes[$id], 'rate' => ($rates[$id] == 0) ? 1 : $rates[$id]);
				$this->db->insert($this->table, $data);
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