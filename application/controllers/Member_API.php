<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_API extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helpers('custom_validator');
        $this->load->model('member_model');
        $this->load->model('admin_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
        if ($this->input->get('token')) {
            $this->member_model->login_token($this->input->get('token'));
        }
        if ($this->input->get('admin_token')) {
            $this->admin_model->login_token($this->input->get('admin_token'));
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
            'required'
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

        if (filter_var($this->input->post('email'), FILTER_VALIDATE_EMAIL)) {
            $info['email'] = $this->input->post('email');
        } else {
            $info['username'] = $this->input->post('email');
        }

        $info['password'] = $this->input->post('password');

        $user = $this->member_model->login($info);

        if (!$user OR !$this->member_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_LOGIN'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'user' => $user,
            'token' => $this->member_model->get_token()
        ]));
        return TRUE;
    }

    public function logout()
    {
        $user = $this->member_model->get_user();

        if (!$this->member_model->logout()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_LOGOUT'
            ]));
            return FALSE; // termination
        }

        $this->output->set_output(json_encode([
            'status' => 'SUCCESS',
            'user' => $user
        ]));
        return TRUE;
    }

    public function find($id = NULL)
    {
        if ($id) {
            $result = $this->member_model->find($id);
        } elseif ($this->input->get('q')) {
            $result = $this->member_model->search($this->input->get('q'));
        } else {
            $result = $this->member_model->latest();
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

        $this->form_validation->set_rules('name', 'Name', [
            'trim', 'required', 'max_length[72]'
        ]);
        $this->form_validation->set_rules('username', 'User Name', [
            'trim', 'required', 'alpha_numeric', 'max_length[24]',
            'is_unique[member.member_username]'
        ]);
        $this->form_validation->set_rules('email', 'Email', [
            'trim', 'required', 'max_length[255]', 'valid_email',
            'is_unique[member.member_email]'
        ]);
        $this->form_validation->set_rules('password', 'Password', [
            'required', 'min_length[7]', 'max_length[60]'
        ]);
        $this->form_validation->set_rules('gender', 'Gender', [
            'required', 'in_list[male,female,unknown,misc]'
        ]);
        $this->form_validation->set_rules('birthday', 'Birthday', [
            'required', 'valid_date'
        ]);
        $this->form_validation->set_message(
            'valid_date',
            'The %s field must contain a valid date.'
        );

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        if (!$this->member_model->add()) {
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

        $authenticated = $this->member_model->is_login()
                       OR $this->admin_model->is_login();

        if (!$authenticated) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $member = $this->member_model->get_user();
        $admin = $this->admin_model->get_user();

        $existing = $this->member_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if (!$admin && $member['member_id'] != $existing['member_id']) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_POLICY'
            ]));
            return FALSE; // termination
        }

        $rules_email = ['trim', 'max_length[255]', 'valid_email'];
        $un_rules = ['trim', 'alpha_numeric', 'max_length[24]'];

        if ($existing['member_email'] !== $this->input->post('email')) {
            $rules_email[] = 'required';
            $rules_email[] = 'is_unique[member.member_email]';
        }
        if ($existing['member_username'] !== $this->input->post('username')) {
            $rules_email[] = 'required';
            $rules_email[] = 'is_unique[member.member_username]';
        }

        $this->form_validation->set_rules('email', 'Email', $rules_email);
        $this->form_validation->set_rules('username', 'User Name', $un_rules);
        $this->form_validation->set_rules('name', 'Name', [
            'trim', 'max_length[72]'
        ]);
        $this->form_validation->set_rules('password', 'Password', [
            'min_length[7]', 'max_length[60]'
        ]);
        $this->form_validation->set_rules('gender', 'Gender', [
            'in_list[male,female,unknown,misc]'
        ]);
        $this->form_validation->set_rules('birthday', 'Birthday', [
            'valid_date'
        ]);
        $this->form_validation->set_message(
            'valid_date',
            'The %s field must contain a valid date.'
        );

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        if (!$this->member_model->update($id)) {
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

        $authenticated = $this->member_model->is_login()
                       OR $this->admin_model->is_login();

        if (!$authenticated) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        $member = $this->member_model->get_user();
        $admin = $this->admin_model->get_user();

        $existing = $this->member_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if (!$admin && ($member['member_id'] != $existing['member_id'])) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_POLICY'
            ]));
            return FALSE; // termination
        }

        $config['upload_path'] = FCPATH
                                .DIRECTORY_SEPARATOR.'uploads'
                                .DIRECTORY_SEPARATOR.'member'
                                .DIRECTORY_SEPARATOR.'photos'
                                .DIRECTORY_SEPARATOR;
        $config['allowed_types'] = 'jpg';
        $config['max_size'] = 500;
        $config['max_width'] = 1024;
        $config['max_height'] = 1024;
        $config['file_name'] = md5(time().'/'.$id);
        $config['file_ext_tolower'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('photo')) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_UPLOAD',
                'errors' => $this->upload->display_errors('', '')
            ]));
            return FALSE; // termination
        }

        if ($existing['member_photo_code']) {
            $old_image = $config['upload_path']
                        .$existing['member_photo_code']
                        .'.jpg';
            if (file_exists($old_image)) {
                unlink($old_image);
            }
            $old_image = $config['upload_path']
                        .$existing['member_photo_code']
                        .'_thumb.jpg';
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        $this->load->library('image_lib');

        $conimg['image_library'] = 'gd2';
        $conimg['source_image'] = $this->upload->data('full_path');
        $conimg['maintain_ratio'] = TRUE;
        $conimg['width'] = 128;
        $conimg['height'] = 128;

        $this->image_lib->initialize($conimg);

        if (!$this->image_lib->resize())
        {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_SYSTEM',
            ]));
            return FALSE; // termination
        }

        $this->image_lib->clear();

        $conimg['width'] = 32;
        $conimg['height'] = 32;
        $conimg['create_thumb'] = TRUE;

        $this->image_lib->initialize($conimg);

        if (!$this->image_lib->resize())
        {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_SYSTEM',
            ]));
            return FALSE; // termination
        }

        $this->image_lib->clear();

        if (!$this->member_model->update_image($id, $config['file_name'])) {
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

        $existing = $this->member_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if (!$this->member_model->delete($id)) {
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
