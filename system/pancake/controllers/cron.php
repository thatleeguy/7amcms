<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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
 * The javascript controller
 *
 * @subpackage	Controllers
 * @category	Javascript
 */
class Cron extends Pancake_Controller {

    public function invoices($password = '') {
        if (!IS_CLI AND PAN::setting('rss_password') !== $password) {
            show_error('Access Denied');
        }

        $this->load->model('invoices/invoice_m');
        echo $this->invoice_m->refresh_reoccurring_invoices();
        echo '-- Finished --';
    }

}

/* End of file cron.php */