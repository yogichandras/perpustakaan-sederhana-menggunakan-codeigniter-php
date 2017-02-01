<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Item_Table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'catalog_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE
            ],
            'member_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE
            ],
            'item_code' => [
                'type' => 'VARCHAR',
                'constraint' => 24,
                'unique' => TRUE
            ],
            'item_expire' => [
                'type' => 'DATE',
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('item_id', TRUE);
        $this->dbforge->create_table('item');
    }

    public function down()
    {
        $this->dbforge->drop_table('item');
    }
}
