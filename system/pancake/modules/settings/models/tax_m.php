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
 * The Tax Model
 *
 * @subpackage	Models
 * @category	Settings
 */
class Tax_m extends Pancake_Model
{

	public function update_taxes($names, $values, $regs)
	{
		$this->db->trans_begin();

		foreach ($names as $id => $name)
		{
			$this->db->where('id', $id);
			
			// Delete if the name is removed
			if ( ! $name)
			{
				$this->db->delete($this->table);
				continue;
			}
			
			$this->db->update($this->table, array(
				'name' => $name,
				'value' => $values[$id],
				'reg' => $regs[$id],
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

	public function insert_taxes($names, $values, $regs)
	{
		$this->db->trans_begin();

		foreach ($names as $id => $name)
		{
			if ($name)
			{
				$this->db->insert($this->table, array(
					'name' => $name,
					'value' => $values[$id],
					'reg' => $regs[$id],
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

/* End of file: tax_m.php */