<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_proposal_email extends CI_Migration {

    public function up()
	{
		$this->db->replace('settings', array(
			'slug' => 'email_new_proposal',
			'value' => "Hi {proposal:client_name}\n\nA new proposal is ready for you on {settings:site_name}:\n\n{proposal:url}\n\nThanks,\n{settings:admin_name}"
		));
    }

    public function down()
	{
	    $this->db
			->where('slug', 'email_new_proposal')
			->delete('settings');
	}
}