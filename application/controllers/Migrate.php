<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        $this->load->library('migration');

        if (!$this->migration->current()) {
            show_error($this->migration->error_string());
        }
    }

    public function seed()
    {
        $this->load->model('admin_model');

        $out = '';

        if (!$this->admin_model->find(1)) {
            $this->admin_model->add('admin', 'admin@example.com', 'admin', 1);
            $out .= "First Admin Created. \n";
        }

        $out .= "Seeded. \n";
        $this->output->set_output($out);
    }
}
