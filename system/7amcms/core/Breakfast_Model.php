<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Breakfast_Model extends CI_Model
{
	/**
	 * @var	string	The name of the table
	 */
	protected $table = FALSE;

	/**
	 * @var	string	The primary ID of the table
	 */
	protected $primary_key = 'id';

	/**
	 *
	 *
	 * @var array	An array of validation rules
	 */
	protected $validate = array();

	/**
	 * @var bool	Whether to skip the auto validation
	 */
	protected $skip_validation = FALSE;

	public function Breakfast_Model()
	{
		self::__construct();
	}

	public function __construct()
	{
		parent::__construct();
		if ( ! $this->table)
		{
			$this->_guess_table();
		}
	}

	public function __call($name, $params)
	{
		$valid_calls = array(
			'get_by'			=> 'get_by_',
			'get_many_by'		=> 'get_many_by_',
			'update_by'			=> 'update_by_',
			'update_many_by'	=> 'update_many_by_',
			'delete_by'			=> 'delete_by_',
			'delete_many_by'	=> 'delete_many_by_',
			'count_by'			=> 'count_by_',
		);

		foreach ($valid_calls as $real_call => $alias_call)
		{
			if (preg_match('/^'.$alias_call.'(.*?)$/i', $name, $matches))
			{
				return call_user_func(array($this, $real_call), $matches[1], $params[0]);
			}
		}
	}


	/**
	 * Get a single record by primary key
	 *
	 * @param	string	The primary key value
	 * @return	object
	 */
	public function get($primary_value)
	{
		return $this->db->where($this->primary_key, $primary_value)
						->get($this->table)
						->row();
	}

	/**
	 * Get a single record by creating a WHERE clause with
	 * the key of $key and the value of $val.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return object
	 * @author Phil Sturgeon
	 */
	public function get_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->get($this->table)
			->row();
	}

	/**
	 * Similar to get_by(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function get_many($primary_value)
	{
		$this->db->where($this->primary_key, $primary_value);
		return $this->get_all();
	}

	/**
	 * Similar to get_by(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function get_many_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->get_all();
	}

	/**
	 * Get all records in the database
	 *
	 * @return array
	 * @author Jamie Rumbelow
	 */
	public function get_all()
	{
		return $this->db->get($this->table)
			->result(); 
	}

	/**
	 * Similar to get_by(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function count_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->count_all_results($this->table);
	}

	/**
	 * Get all records in the database
	 *
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function count_all()
	{
		return $this->db->count_all($this->table);
	}

	/**
	 * Insert a new record into the database,
	 * calling the before and after create callbacks.
	 * Returns the insert ID.
	 *
	 * @param array $data Information
	 * @return integer
	 * @author Jamie Rumbelow
	 * @modified Dan Horrigan
	 */
	public function insert($data, $skip_validation = FALSE)
	{
		if ($skip_validation or $this->validate($data))
		{
			$this->db->insert($this->table, $data);
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Similar to insert(), just passing an array to insert
	 * multiple rows at once. Returns an array of insert IDs.
	 *
	 * @param array $data Array of arrays to insert
	 * @return array
	 * @author Jamie Rumbelow
	 */
	public function insert_many($data, $skip_validation = FALSE)
	{
		$ids = array();

		foreach ($data as $row)
		{
			if ($skip_validation or $this->validate($data))
			{
				$data = $this->_run_before_create($row);
				$this->db->insert($this->table, $row);
				$this->_run_after_create($row, $this->db->insert_id());

				$ids[] = $this->db->insert_id();
			}
			else
			{
				$ids[] = FALSE;
			}
		}

		$this->skip_validation = FALSE;
		return $ids;
	}

	/**
	 * Update a record, specified by an ID.
	 *
	 * @param integer $id The row's ID
	 * @param array $array The data to update
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function update($primary_value, $data, $skip_validation = FALSE)
	{

		if($skip_validation or $this->validate($data))
		{
			return $this->db->where($this->primary_key, $primary_value)
				->set($data)
				->update($this->table);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Update a record, specified by $key and $val.
	 *
	 * @param string $key The key to update with
	 * @param string $val The value
	 * @param array $array The data to update
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function update_by()
	{
		$args =& func_get_args();
		$data = array_pop($args);
		$this->_set_where($args);

		if($this->validate($data))
		{
			$this->skip_validation = FALSE;
			return $this->db->set($data)
				->update($this->table);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Updates many records, specified by an array
	 * of IDs.
	 *
	 * @param array $primary_values The array of IDs
	 * @param array $data The data to update
	 * @return bool
	 * @author Phil Sturgeon
	 */
	public function update_many($primary_values, $data, $skip_validation)
	{
		$valid = TRUE;
		if($skip_validation === FALSE)
		{
			$valid = $this->validate($data);
		}

		if($valid)
		{
			$this->skip_validation = FALSE;
			return $this->db->where_in($this->primary_key, $primary_values)
				->set($data)
				->update($this->table);

		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Updates all records
	 *
	 * @param array $data The data to update
	 * @return bool
	 * @since 1.1.3
	 * @author Phil Sturgeon
	 */
	public function update_all($data)
	{
		return $this->db->set($data)
			->update($this->table);
	}

	/**
	 * Delete a row from the database table by the
	 * ID.
	 *
	 * @param integer $id
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function delete($id)
	{
		return $this->db->where($this->primary_key, $id)
			->delete($this->table);
	}

	/**
	 * Delete a row from the database table by the
	 * key and value.
	 *
	 * @param	string	The 'WHERE' column
	 * @param	string	The 'WHERE' value
	 * @return	bool
	 */
	public function delete_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->delete($this->table);
	}

	/**
	 * Delete many rows from the table where the primary
	 * key is in the array of values
	 *
	 * @access	public
	 * @param	array	The array of primary key values
	 * @return	bool
	 */
	public function delete_many($values)
	{
		return $this->db->where_in($this->primary_key, $values)->delete($this->table);
	}

	/**
	* Orders the the results by some criteria
	*
	* @param	string	The order by criteria
	* @param	string	The order to return them in
	* @return	object	$this
	*/
	public function order_by($criteria, $order = 'ASC')
	{
		$this->db->order_by($criteria, $order);
		return $this;
	}

	/**
	* Limits and offsets the results
	*
	* @access	public
	* @param	int		The number of rows
	* @param	int		The offset
	* @return	object	$this
	*/
	public function limit($limit, $offset = 0)
	{
		$this->db->limit($limit, $offset);
		return $this;
	}


	/**
	* Limits and offsets the results
	*
	* @access	public
	* @param	int		The number of rows
	* @param	int		The offset
	* @return	object	$this
	*/
	public function select($fields, $escape = false)
	{
		$this->db->select($fields, $escape);
		return $this;
	}


	/**
	* Limits and offsets the results
	*
	* @access	public
	* @param	int		The number of rows
	* @param	int		The offset
	* @return	object	$this
	*/
	public function where()
	{
		$args =& func_get_args();
		call_user_func_array(array($this->db, 'where'), $args);

		return $this;
	}

	/**
	 * Runs validation on the passed data.  Also used to turn off
	 * validation like this:
	 *
	 * $this->validate(FALSE);
	 *
	 * @access	protected
	 * @param	array	The data to validate
	 * @return	bool
	 */
	public function validate($data)
	{
		if ($this->skip_validation or empty($this->validate))
		{
			return TRUE;
		}
		
		if (empty($data))
		{
			return FALSE;
		}

		foreach($data as $key => $val)
		{
			$_POST[$key] = $val;
		}

		$this->load->library('form_validation');
		if (is_array($this->validate))
		{
			$this->form_validation->set_rules($this->validate);
			return $this->form_validation->run();
		}
		else
		{
			return $this->form_validation->run($this->validate);
		}
	}

	/**
	 * Guesses the table name
	 *
	 * @access	public
	 * @return	void
	 */
	private function _guess_table()
	{
		$this->load->helper('inflector');
		$class = preg_replace('/(_m|_model)?$/', '', get_class($this));

		$this->table = plural(strtolower($class));
	}


	/**
	 * Sets the where from given paramters
	 *
	 * @access	private
	 * @param	array	An array of parameters
	 * @return	void
	 */
	private function _set_where($params)
	{
		if(is_array($params[0]))
		{
			$this->db->where($params[0]);
			return;
		}
		$this->db->where($params[0], $params[1]);
	}
}