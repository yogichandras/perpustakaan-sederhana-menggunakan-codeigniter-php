<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Member_Table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'member_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'member_username' => [
                'type' => 'VARCHAR',
                'constraint' => 24,
                'unique' => TRUE
            ],
            'member_name' => [
                'type' => 'VARCHAR',
                'constraint' => 72
            ],
            'member_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => TRUE
            ],
            'member_password' => [
                'type' => 'VARCHAR',
                'constraint' => 72
            ],
            'member_gender' => [
                'type' => 'ENUM("male", "female", "unknown", "misc")',
                'default' => 'misc',
                'null' => FALSE
            ],
            'member_photo_code' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => TRUE
            ],
            'member_description' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'member_birthday' => [
                'type' => 'DATE'
            ],
            'member_hometown' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ],
            'member_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('member_id', TRUE);
        $this->dbforge->create_table('member');
    }

    public function down()
    {
        $this->dbforge->drop_table('member');
    }
}
