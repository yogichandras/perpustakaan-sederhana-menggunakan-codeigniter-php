<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Admin_Table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'admin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'admin_name' => [
                'type' => 'VARCHAR',
                'constraint' => '72'
            ],
            'admin_email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE
            ],
            'admin_password' => [
                'type' => 'VARCHAR',
                'constraint' => 72
            ]
        ]);
        $this->dbforge->add_key('admin_id', TRUE);
        $this->dbforge->create_table('admin');
    }

    public function down()
    {
        $this->dbforge->drop_table('admin');
    }
}
