<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends CI_Controller
{
    public function index()
    {
        $this->output->enable_profiler(true);
        $this->load->view('catalog');
    }
}
