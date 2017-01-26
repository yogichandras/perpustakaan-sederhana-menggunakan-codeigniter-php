<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Catalog_Table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'catalog_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'catalog_register' => [
                'type' => 'VARCHAR',
                'constraint' => 24,
                'unique' => TRUE
            ],
            'catalog_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'catalog_description' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'catalog_keywords' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'catalog_author' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'Unknown'
            ],
            'catalog_registered_at' => [
                'type' => 'TIMESTAMP'
            ],
            'catalog_image_code' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('catalog_id', TRUE);
        $this->dbforge->create_table('catalog');
    }

    public function down()
    {
        $this->dbforge->drop_table('catalog');
    }
}
