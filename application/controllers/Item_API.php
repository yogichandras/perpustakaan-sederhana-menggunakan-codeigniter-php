<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_API extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helpers('custom_validator');
        $this->load->model('item_model');
        $this->load->model('catalog_model');
        $this->load->model('member_model');
        $this->load->model('admin_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
        if ($this->input->get('admin_token')) {
            $this->admin_model->login_token($this->input->get('admin_token'));
        }
    }

    public function find($id)
    {
        $result = $this->item_model->find($id);

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

    public function find_by_member($member_id)
    {
        $result = $this->item_model->find_by_member($member_id);

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

    public function find_by_catalog($catalog_id)
    {
        $result = $this->item_model->find_by_catalog($catalog_id);

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

    public function find_by_code($code)
    {
        $result = $this->item_model->find_by_code($code);

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

    public function find_expired()
    {
        $result = $this->item_model->find_expired($this->input->get('date'));

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

    public function add($catalog_id)
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

        $this->form_validation->set_rules('code', 'Code', [
            'required', 'max_length[24]'
        ]);

        if (!$this->form_validation->run()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_VALIDATION',
                'validation' => $this->form_validation->error_array()
            ]));
            return FALSE; // termination
        }

        if (!$this->item_model->add($catalog_id, $this->input->post('code'))) {
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

    public function borrow_item($id, $member_id)
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

        $this->form_validation->set_rules('expire', 'Expire', [
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

        $existing = $this->item_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if ($existing['member_id']) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_UNABLE'
            ]));
            return FALSE; // termination
        }

        if (!$this->member_model->find($member_id)) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        $date = $this->input->post('expire');

        if (!$this->item_model->borrow_item($id, $member_id, $date)) {
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

    public function return_item($id, $member_id)
    {
        if (!$this->admin_model->is_login()) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_AUTH'
            ]));
            return FALSE; // termination
        }

        if (!$this->item_model->return_item($id, $member_id)) {
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

        $existing = $this->item_model->find($id);

        if (!$existing) {
            $this->output->set_output(json_encode([
                'status' => 'ERROR_NORESULT'
            ]));
            return FALSE; // termination
        }

        if (!$this->item_model->delete($id)) {
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
