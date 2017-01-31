<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_API extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('catalog_model');
        $this->load->model('admin_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
        if ($this->input->get('admin_token')) {
            $this->admin_model->login_token($this->input->get('admin_token'));
        }
    }

    public function all()
    {
        $result = $this->catalog_model->all();

        if (!$result) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'values' => $result
        ]));
        return TRUE;
    }

    public function find($id = NULL)
    {
        if ($id) {
            $result = $this->catalog_model->find($id);
        } elseif ($this->input->get('q')) {
            $result = $this->catalog_model->search($this->input->get('q'));
        } else {
            $result = $this->catalog_model->latest();
        }

        if (!$result) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'values' => $result
        ]));
        return TRUE;
    }

    public function add()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_REQUEST'
            ]));
            return FALSE; // termination
        }

        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $this->form_validation->set_rules('register', 'Registration Number', [
            'trim', 'required', 'alpha_dash', 'max_length[24]',
            'is_unique[catalog.catalog_register]'
        ]);
        $this->form_validation->set_rules('title', 'Title', [
            'trim', 'required', 'max_length[255]'
        ]);
        $this->form_validation->set_rules('author', 'Author', [
            'trim', 'max_length[100]'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        if (!$this->catalog_model->add()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_DATABASE'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS'
        ]));
        return TRUE;
    }

    public function update($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_REQUEST'
            ]));
            return FALSE; // termination
        }

        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $existing = $this->catalog_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $rules_register = ['trim', 'alpha_dash', 'max_length[24]'];

        if ($existing['catalog_register'] !== $this->input->post('register')) {
            $rules_register[] = 'required';
            $rules_register[] = 'is_unique[catalog.catalog_register]';
        }

        $this->form_validation->set_rules(
            'register',
            'Registration Number',
            $rules_register
        );

        $this->form_validation->set_rules('title', 'Title', [
            'trim', 'max_length[255]'
        ]);

        $this->form_validation->set_rules('author', 'Author', [
            'trim', 'max_length[100]'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        if (!$this->catalog_model->update($id)) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_DATABASE'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS'
        ]));
        return TRUE;
    }

    public function update_image($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_REQUEST'
            ]));
            return FALSE; // termination
        }

        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $existing = $this->catalog_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $config['upload_path'] = FCPATH
                                .DIRECTORY_SEPARATOR.'uploads'
                                .DIRECTORY_SEPARATOR.'catalog'
                                .DIRECTORY_SEPARATOR.'images'
                                .DIRECTORY_SEPARATOR;
        $config['allowed_types'] = 'jpg';
        $config['max_size'] = 500;
        $config['max_width'] = 1024;
        $config['max_height'] = 768;
        $config['file_name'] = md5(time().'/'.$id);
        $config['file_ext_tolower'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('image')) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_UPLOAD',
                'errors' => $this->upload->display_errors('', '')
            ]));
            return FALSE; // termination
        }

        if ($existing['catalog_image_code']) {
            $old_image = $config['upload_path']
                        .$existing['catalog_image_code']
                        .'.jpg';
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        if (!$this->catalog_model->update_image($id, $config['file_name'])) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_DATABASE'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS'
        ]));
        return TRUE;
    }

    public function save($id = NULL)
    {
        return $this->catalog_model->save($id);
    }

    public function delete($id)
    {
        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $existing = $this->catalog_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if ($existing['catalog_image_code']) {
            $old_image = FCPATH
                        .DIRECTORY_SEPARATOR.'uploads'
                        .DIRECTORY_SEPARATOR.'catalog'
                        .DIRECTORY_SEPARATOR.'images'
                        .DIRECTORY_SEPARATOR
                        .$existing['catalog_image_code']
                        .'.jpg';
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        if (!$this->catalog_model->delete($id)) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_DATABASE'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'values' => $existing
        ]));
        return TRUE;
    }
}