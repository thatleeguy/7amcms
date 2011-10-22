<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bump_to_3_1_5 extends CI_Migration {
    
	public function up()
	{
        Settings::setVersion('3.1.5');
		
		
		
    }
    
    public function down()
	{
        Settings::setVersion('3.1.4');
    }
}