<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    protected $user;

    public function __construct()
    {
        parent::__construct();

        $this->config->load('auth');
        $this->load->helpers('base64url');
        $this->load->library('session');

        if ($this->session->has_userdata('admin_id')) {
            $this->user = $this->find($this->session->userdata('admin_id'));
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

    public function login($email, $password, $keep = FALSE)
    {
        $this->db->where('admin_email', $email);
        $query = $this->db->get('admin');
        $user = $query->row_array();

        if (!$user) {
            return FALSE; // termination
        }

        if (!$this->verify($password, $user['admin_password'])) {
            return FALSE; // termination
        }

        unset($user['admin_password']);
        $this->user = $user;
        $this->session->set_userdata('admin_id', $this->user['admin_id']);

        if ($keep) {
        }

        return $this->user;
    }

    public function logout()
    {
        $this->session->unset_userdata('admin_id');
        $this->user = FALSE;

        if ($this->user OR $this->session->has_userdata('admin_id')) {
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
            $this->config->item('admin_secret')
        );
    }

    public function get_token()
    {
        $header = base64url_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));
        $payload = base64url_encode(json_encode([
            'admin_id' => $this->user['admin_id'],
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

        if (empty($payload['admin_id'])) {
            return FALSE; // termination
        }

        $this->db->where('admin_id', $payload['admin_id']);
        $query = $this->db->get('admin');

        $user = $query->row_array();

        if (!$user) {
            return FALSE; // termination
        }

        $this->user = $user;
        return $this->user;
    }

    public function add($name, $email, $password, $id = NULL)
    {
        if ($id) {
            $this->db->set('admin_id', $id);
        }
        $this->db->set('admin_name', $name);
        $this->db->set('admin_email', strtolower($email));
        $this->db->set('admin_password', $this->hash($password));
        return $this->db->insert('admin');
    }

    public function all()
    {
        $this->db->select('admin_id, admin_name, admin_email');
        $query = $this->db->get('admin');
        return $query->result_array();
    }

    public function find($id)
    {
        $this->db->select('admin_id, admin_name, admin_email');
        $this->db->where('admin_id', $id);
        $query = $this->db->get('admin', 1);
        return $query->row_array();
    }

    public function update($id, $changes)
    {
        if (!empty($changes['name'])) {
            $this->db->set('admin_name', $changes['name']);
        }

        if (!empty($changes['email'])) {
            $this->db->set('admin_email', strtolower($changes['email']));
        }

        if (!empty($changes['password'])) {
            $this->db->set('admin_password', $this->hash($changes['password']));
        }

        $this->db->where('admin_id', $id);
        return $this->db->update('admin');
    }

    public function delete($id)
    {
        if ($id == 1) {
            return FALSE; // termination
        }

        $this->db->where('admin_id', $id);
        return $this->db->delete('admin');
    }
}
