<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_model extends CI_Model
{
    public function add($catalog_id, $item_code)
    {
        $this->db->set('catalog_id', $catalog_id);
        $this->db->set('item_code', $item_code);
        return $this->db->insert('item');
    }

    public function find($id)
    {
        $this->db->select(
            'item.*, catalog.*, member.member_id, member_name, member_username,'
            .' member_photo_code'
        );
        $this->db->join(
            'catalog',
            'catalog.catalog_id = item.catalog_id',
            'left'
        );
        $this->db->join('member', 'member.member_id = item.member_id', 'left');
        $this->db->where('item_id', $id);
        return $this->db->get('item')->row_array();
    }

    public function find_by_member($member_id)
    {
        $this->db->join(
            'catalog',
            'catalog.catalog_id = item.catalog_id',
            'left'
        );
        $this->db->where('member_id', $member_id);
        return $this->db->get('item')->result_array();
    }

    public function find_by_catalog($catalog_id)
    {
        $this->db->select(
            'item.*, member.member_id, member_name, member_username,'
            .' member_photo_code'
        );
        $this->db->join('member', 'member.member_id = item.member_id', 'left');
        $this->db->where('catalog_id', $catalog_id);
        return $this->db->get('item')->result_array();
    }

    public function find_by_code($code)
    {
        $this->db->select(
            'item.*, catalog.*, member.member_id, member_name, member_username,'
            .' member_photo_code'
        );
        $this->db->join(
            'catalog',
            'catalog.catalog_id = item.catalog_id',
            'left'
        );
        $this->db->join('member', 'member.member_id = item.member_id', 'left');
        $this->db->where('item_code', $code);
        return $this->db->get('item')->row_array();
    }

    public function find_expired($date = NULL)
    {
        $this->db->select(
            'item.*, catalog.*, member.member_id, member_name, member_username,'
            .' member_photo_code'
        );
        $this->db->join(
            'catalog',
            'catalog.catalog_id = item.catalog_id',
            'left'
        );
        $this->db->join('member', 'member.member_id = item.member_id', 'left');
        $date = $date? $date : date('Y-m-d', time());
        $this->db->where('item_expire <>', NULL);
        $this->db->where('item_expire <', $date);
        return $this->db->get('item')->result_array();
    }

    public function borrow_item($id, $member_id, $expire)
    {
        if (!$id OR !$member_id OR !$expire) {
            return FALSE;
        }

        $this->db->where('item_id', $id);
        $this->db->where('member_id', NULL);
        $this->db->set('member_id', $member_id);
        $this->db->set('item_expire', $expire);
        return $this->db->update('item');
    }

    public function return_item($id, $member_id)
    {
        $this->db->where('item_id', $id);
        $this->db->where('member_id', $member_id);
        $this->db->set('member_id', NULL);
        $this->db->set('item_expire', NULL);
        return $this->db->update('item');
    }

    public function delete($id)
    {
        $this->db->where('item_id', $id);
        return $this->db->delete('item');
    }
}
