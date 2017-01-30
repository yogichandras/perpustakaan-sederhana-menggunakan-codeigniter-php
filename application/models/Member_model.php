<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends CI_Model
{
    protected $user;

    public function __construct()
    {
        parent::__construct();

        $this->config->load('auth');
        $this->load->helpers('base64url');
        $this->load->library('session');

        if ($this->session->has_userdata('member_id')) {
            $this->user = $this->find($this->session->userdata('member_id'));
        }
    }

    protected function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    protected function verify($password, $hashed)
    {
        return password_verify($password, $hashed);
    }

    protected function query_by_identifier($identifier)
    {
        if (is_numeric($identifier)) {
            $identifier = $identifier + 0;
        }

        if (is_int($identifier)) {
            $this->db->where('member_id', $identifier);
        } else {
            $this->db->where('member_username', $identifier);
        }
    }

    public function login($info)
    {
        $missing_info = empty($info['email'])
                      OR empty($info['username'])
                      OR empty('password');
        if ($missing_info) {
            return FALSE; // termination
        }

        if (!empty($info['email'])) {
            $this->db->where('member_email', $info['email']);
        } else {
            $this->db->where('member_username', $info['username']);
        }

        $query = $this->db->get('member');
        $user = $query->row_array();

        if (!$user) {
            return FALSE; // termination
        }

        if (!$this->verify($info['password'], $user['member_password'])) {
            return FALSE; // termination
        }

        unset($user['member_password']);
        $this->user = $user;
        $this->session->set_userdata('member_id', $this->user['member_id']);

        if (!empty($info['keep']) && $info['keep']) {
        }

        return $this->user;
    }

    public function logout()
    {
        $this->session->unset_userdata('member_id');
        $this->user = FALSE;

        if ($this->user OR $this->session->has_userdata('member_id')) {
            return FALSE; // termination
        }

        return TRUE;
    }

    public function get_user()
    {
        return $this->user;
    }

    public function is_login()
    {
        return $this->user ? TRUE : FALSE;
    }

    protected function create_signature($header, $payload)
    {
        return hash_hmac(
            'sha256',
            $header.'.'.$payload,
            $this->config->item('member_secret')
        );
    }

    public function get_token()
    {
        $header = base64url_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));
        $payload = base64url_encode(json_encode([
            'member_id' => $this->user['member_id'],
            'timestamp' => date('Y-m-d H:i:s', time())
        ]));
        $signature = $this->create_signature($header, $payload);

        return $header.'.'.$payload.'.'.$signature;
    }

    public function login_token($token)
    {
        $token = explode('.', $token);

        if (count($token) < 3) {
            return FALSE; // termination
        }

        $header = json_decode(base64url_decode($token[0]), TRUE);
        $payload = json_decode(base64url_decode($token[1]), TRUE);

        if (empty($header['alg']) OR $header['alg'] !== 'HS256') {
            return FALSE; // termination
        }

        if ($token[2] !== $this->create_signature($token[0], $token[1])) {
            return FALSE; // termination
        }

        if (empty($payload['member_id'])) {
            return FALSE; // termination
        }

        $this->db->where('member_id', $payload['member_id']);
        $query = $this->db->get('member');

        $user = $query->row_array();

        if (!$user) {
            return FALSE; // termination
        }

        unset($user['member_password']);
        $this->user = $user;
        return $this->user;
    }

    public function set_from_post()
    {
        if ($this->input->post('name')) {
            $this->db->set('member_name', $this->input->post('name'));
        }
        if ($this->input->post('username')) {
            $this->db->set('member_username', $this->input->post('username'));
        }
        if ($this->input->post('email')) {
            $this->db->set('member_email', strtolower(
                $this->input->post('email')
            ));
        }
        if ($this->input->post('password')) {
            $this->db->set('member_password', $this->hash(
                $this->input->post('password')
            ));
        }
        if ($this->input->post('gender')) {
            $this->db->set('member_gender', $this->input->post('gender'));
        }
        if ($this->input->post('birthday')) {
            $this->db->set('member_birthday', $this->input->post('birthday'));
        }
        if ($this->input->post('hometown')) {
            $this->db->set('member_hometown', $this->input->post('hometown'));
        }
        if ($this->input->post('address')) {
            $this->db->set('member_address', $this->input->post('address'));
        }
    }

    public function add()
    {
        $this->set_from_post();
        return $this->db->insert('member');
    }

    public function all()
    {
        $this->db->select(
            'member_id, member_name, member_username, member_email,'
            .' member_gender, member_photo_code'
        );
        $query = $this->db->get('member');
        return $query->result_array();
    }

    public function find($id)
    {
        $this->query_by_identifier($id);
        $query = $this->db->get('member', 1);
        $result = $query->row_array();
        unset($result['member_password']);
        return $result;
    }

    public function search($keyword, $perpage = 0, $page = 1)
    {
        $this->db->like('member_name', $keyword);
        $this->db->or_like('member_username', $keyword);
        $this->db->or_like('member_description', $keyword);
        $this->db->or_like('member_address', $keyword);
        $this->db->or_like('member_hometown', $keyword);

        return $this->latest($perpage, $page);
    }

    public function latest($perpage = 0, $page = 1)
    {
        $this->db->select(
            'member_id, member_name, member_username, member_gender,'
            .' member_photo_code'
        );
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
            $query = $this->db->get('member', $perpage, ($page-1) * $perpage);
        } else {
            $query = $this->db->get('member', $perpage);
        }

        return $query->result_array();
    }

    public function update($id)
    {
        $this->set_from_post();
        $this->query_by_identifier($id);
        return $this->db->update('member');
    }

    public function update_image($id, $code)
    {
        $this->db->set('member_photo_code', $code);
        $this->query_by_identifier($id);
        return $this->db->update('member');
    }

    public function delete($id)
    {
        $this->query_by_identifier($id);
        return $this->db->delete('member');
    }
}
