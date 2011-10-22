<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_project_unique_ids_and_invoice_project extends CI_Migration {

    public function up() {
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('projects') . " LIKE 'unique_id'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'unique_id') {
            $this->dbforge->add_column('projects', array(
                'unique_id' => array(
                    'type' => 'varchar',
                    'constraint' => 10,
                    'null' => FALSE,
                    'default' => '',
                ),
            ));
            
            $this->load->model('projects/project_m');
        
            foreach ($this->db->get('projects')->result_array() as $row) {
                $unique_id = $this->project_m->_generate_unique_id();
                $this->db->where('id', $row['id'])->update('projects', array('unique_id' => $unique_id));
            }
        }
        
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoices') . " LIKE 'project_id'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'project_id') {
            $this->dbforge->add_column('invoices', array(
                'project_id' => array(
                    'type' => 'int',
                    'constraint' => 10,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
        
        
    }

    public function down() {
        
    }

}