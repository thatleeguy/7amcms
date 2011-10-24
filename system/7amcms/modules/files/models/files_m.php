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
 * The Files Model
 *
 * @subpackage	Models
 * @category	Files
 */
class Files_m extends Breakfast_Model
{
	/**
	 * @var	string	The name of the files table
	 */
	protected $table = 'files';

	public function get_by_unique_id($unique_id)
	{
		return $this->db->where('invoice_unique_id', $unique_id)->get($this->table)->result_array();
	}

	/**
	 * Uploads the files.
	 *
	 * @access	public
	 * @param	array	The $_FILES['input_name']
	 * @param	string	The unique id
	 * @return	void
	 */
	public function upload($input, $unique_id)
	{
            $return = array();
		if (count($input['name']) > 0)
		{
			if ($unique_id != 'settings') {
			    $folder_name = sha1(time().$unique_id).'/';
			} else {
			    $folder_name = 'branding/';
			}

			for ($i = 0; $i < count($input['name']); $i++)
			{
				if (empty($input['name'][$i]))
				{
					continue;
				}
				if ( ! is_dir('uploads/'.$folder_name))
				{
					mkdir('uploads/'.$folder_name);
				}
				$real_name = basename($input['name'][$i]);
				$target_path = 'uploads/'.$folder_name.$real_name;
				if (move_uploaded_file($input['tmp_name'][$i], $target_path))
				{
                                    if ($unique_id != 'settings') {
					$result = parent::insert(array(
						'invoice_unique_id'	=> $unique_id,
						'orig_filename'		=> $real_name,
						'real_filename'		=> $folder_name.$real_name
					));
                                    } else {
					$base_without_index = (substr(base_url(), -10) == 'index.php/') ? substr(base_url(), 0, strlen(base_url()) - 10).'/' : base_url();
                                        $return[$real_name] = $base_without_index.'uploads/'.$folder_name.rawurlencode($real_name);
                                    }
				}
				else
				{
					return FALSE;
				}
			}
		}
                if ($unique_id != 'settings') {
                    return TRUE;
                } else {
                    return $return;
                }
	}

	public function delete($file_id)
	{
		$file = parent::get($file_id);
                if (!empty($file)) {
                    parent::delete($file_id);

                    if (is_file('uploads/'.$file->real_filename))
                    {
                            @unlink('uploads/'.$file->real_filename);
                            $parts = explode('/', $file->real_filename);
                            @rmdir($parts[0]);
                    }
                }
	}

}

/* End of file: settings_m.php */