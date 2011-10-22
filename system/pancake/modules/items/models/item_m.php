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
 * The Item Model
 *
 * @subpackage	Models
 * @category	Items
 */
class Item_m extends Pancake_Model
{
	protected $validate = array(
		array(
			'field'	  => 'name',
			'label'	  => 'lang:global:name',
			'rules'	  => 'required|max_length[255]',
		),
		array(
			'field'	  => 'description',
			'label'	  => 'lang:global:description',
			'rules'	  => '',
		),
		array(
			'field'	  => 'qty',
			'label'	  => 'lang:items:quantity',
			'rules'	  => 'required|numeric',
		),
		array(
			'field'	  => 'rate',
			'label'	  => 'lang:tasks:rate',
			'rules'	  => 'required|numeric',
		),
		array(
			'field'	  => 'tax_id',
			'label'	  => 'lang:items:tax_rate',
			'rules'	  => 'required|numeric',
		),
	);
}

/* End of file: item_m.php */