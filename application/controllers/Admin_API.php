<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_API extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('admin_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
        if ($this->input->get('admin_token')) {
            $this->admin_model->login_token($this->input->get('token'));
        }
    }

    public function login()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_REQUEST'
            ]));
            return FALSE; // termination
        }

        $this->form_validation->set_rules('email', 'Email', [
            'required', 'valid_email'
        ]);
        $this->form_validation->set_rules('password', 'Password', [
            'required'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        $user = $this->admin_model->login(
            $this->input->post('email'),
            $this->input->post('password')
        );

        if (!$user OR !$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_LOGIN'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'user' => $user,
            'token' => $this->admin_model->get_token()
        ]));
        return TRUE;
    }

    public function all()
    {
        $result = $this->admin_model->all();

        if (!$result) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'values' => $result,
            'user' => $this->admin_model->get_user()
        ]));
        return TRUE;
    }

    public function find($id)
    {
        $result = $this->admin_model->find($id);

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

        $admin = $this->admin_model->get_user();

        if ($admin['admin_id'] != 1) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_POLICY'
            ]));
            return FALSE; // termination
        }

        $this->form_validation->set_rules('name', 'Name', [
            'trim', 'required', 'max_length[72]'
        ]);
        $this->form_validation->set_rules('email', 'Email', [
            'trim', 'required', 'max_length[255]',
            'is_unique[admin.admin_email]'
        ]);
        $this->form_validation->set_rules('password', 'Password', [
            'required', 'min_length[7]', 'max_length[60]'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        $is_added = $this->admin_model->add(
            $this->input->post('name'),
            $this->input->post('email'),
            $this->input->post('password')
        );

        if (!$is_added) {
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
            $this->output->set_output(['status' => 'ERROR_REQUEST']);
            return FALSE; // termination
        }

        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $admin = $this->admin_model->get_user();

        $existing = $this->admin_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if ($admin['admin_id'] != $existing['admin_id']) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_POLICY'
            ]));
            return FALSE; // termination
        }

        $rules_email = ['trim', 'max_length[255]'];

        if ($existing['admin_email'] !== $this->input->post('email')) {
            $rules_email[] = 'required';
            $rules_email[] = 'is_unique[admin.admin_email]';
        }

        $this->form_validation->set_rules('email', 'Email', $rules_email);
        $this->form_validation->set_rules('name', 'Name', [
            'trim', 'max_length[72]'
        ]);
        $this->form_validation->set_rules('password', 'Password', [
            'min_length[7]', 'max_length[60]'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        $changes = [
            'name' => $this->input->post('name'),
            'email' => $this->input->post('email'),
            'password' => $this->input->post('password')
        ];

        if (!$this->admin_model->update($id, $changes)) {
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

    public function delete($id)
    {
        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $admin = $this->admin_model->get_user();

        if ($admin['admin_id'] != 1) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_POLICY'
            ]));
            return FALSE; // termination
        }

        $existing = $this->admin_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if (!$this->admin_model->delete($id)) {
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