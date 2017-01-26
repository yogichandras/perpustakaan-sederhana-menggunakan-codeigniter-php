<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_model extends CI_Model
{
    public function all()
    {
        $query = $this->db->get('catalog');
        return $query->result_array();
    }

    public function find($id)
    {
        $this->db->where('catalog_id', $id);
        $query = $this->db->get('catalog', 1);
        return $query->row_array();
    }

    public function search($keyword, $perpage = 0, $page = 1)
    {
        $this->db->like('catalog_register', $keyword);
        $this->db->or_like('catalog_title', $keyword);
        $this->db->or_like('catalog_author', $keyword);
        $this->db->or_like('catalog_keywords', $keyword);
        $this->db->or_like('catalog_description', $keyword);

        return $this->latest($perpage, $page);
    }

    public function latest($perpage = 0, $page = 1)
    {
        if (!$perpage && $this->input->get('pp')) {
            $perpage = $this->input->get('pp');
        }

        if ($page <= 1 && $this->input->get('p')) {
            $page = $this->input->get('p');
        }

        if ($perpage < 1) {
            $perpage = 100;
        }

        if ($page > 1) {
            $query = $this->db->get('catalog', $perpage, ($page-1) * $perpage);
        } else {
            $query = $this->db->get('catalog', $perpage);
        }

        return $query->result_array();
    }

    protected function set_from_post()
    {
        if ($this->input->post('register')) {
            $this->db->set(
                'catalog_register',
                $this->input->post('register')
            );
        }

        if ($this->input->post('title')) {
            $this->db->set(
                'catalog_title',
                ucfirst($this->input->post('title'))
            );
        }

        if ($this->input->post('author')) {
            $this->db->set(
                'catalog_author',
                $this->input->post('author')
            );
        }

        if ($this->input->post('description')) {
            $this->db->set(
                'catalog_description',
                $this->input->post('description')
            );
        }

        if ($this->input->post('keywords')) {
            $this->db->set(
                'catalog_keywords',
                $this->input->post('keywords')
            );
        }
    }

    public function add()
    {
        $this->set_from_post();
        $this->db->set('catalog_registered_at', date('Y-m-d H:i:s', time()));
        return $this->db->insert('catalog');
    }

    public function update($id)
    {
        $this->set_from_post();
        $this->db->where('catalog_id', $id);
        return $this->db->update('catalog');
    }

    public function save($id = NULL)
    {
        if ($id) {
            $this->update($id);
        } else {
            $this->add();
        }
    }

    public function update_image($id, $code)
    {
        $this->db->set('catalog_image_code', $code);
        $this->db->where('catalog_id', $id);
        return $this->db->update('catalog');
    }

    public function delete($id)
    {
        $this->db->where('catalog_id', $id);
        return $this->db->delete('catalog');
    }
}