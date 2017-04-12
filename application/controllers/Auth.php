<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{

    function __construct() {
        parent::__construct();
    }

    public function index() {

        if($this->session->userdata('logged_in')) {
            //go to default page
            redirect(base_url().'home');
        }

        $data = array();
        $data['login_url'] = base_url()."auth/login";

        $this->load->view('auth/login-2', $data);
    }

    public function login() {
        $username = $this->security->xss_clean($this->input->post('username'));
        $password = $this->security->xss_clean($this->input->post('password'));

        if(empty($username) or empty($password)) {
            $this->session->set_flashdata('error_message','Username atau password harus diisi');
            redirect(base_url().'auth/index');
        }

        $sql = "select * from bizhub.users where user_name = ?";

        $query = $this->db->query($sql, array($username));
        $row = $query->row_array();

        $md5pass = md5(trim($password));


        if($row['user_status'] != 1) {
            $this->session->set_flashdata('error_message','Maaf, User yang bersangkutan sudah tidak aktif. Silahkan hubungi administrator.');
            redirect(base_url().'auth/index');
        }

        $ldap_status = 'NO';

        if(trim($row['user_password']) == "") { /* Cek LDAP Login */
            $this->load->model('administration/ldap_connection');
            /* Open LDAP Connection */
            $auth = $this->ldap_connection->Open($username, $password);
            if ($auth == 1) { /* authentifikasi LDAP Telkom berhasil */
                //do nothing
                $ldap_status = 'YES';
            }else {
                $this->session->set_flashdata('error_message','Username atau password LDAP Anda salah');
                redirect(base_url().'auth/index');
            }
        }else {
            if( strcmp($md5pass, trim($row['user_password'])) != 0 ) {
                $this->session->set_flashdata('error_message','Username atau password Anda salah');
                redirect(base_url().'auth/index');
            }
        }


        $userdata = array(
                        'user_id'           => $row['user_id'],
                        'user_name'         => $row['user_name'],
                        'user_email'        => $row['user_email'],
                        'user_full_name'    => $row['user_full_name'],
                        'is_ldap'           => $ldap_status,
                        'wh_id'             => $row['wh_id'],
                        'logged_in'         => true
                      );

        $this->session->set_userdata($userdata);
        redirect(base_url().'home');
    }

    public function logout() {

        $userdata = array(
                        'user_id'               => '',
                        'user_name'             => '',
                        'user_email'            => '',
                        'user_full_name'        => '',
                        'wh_id'                 => '',
                        'logged_in'             => false,
                      );

        $this->session->unset_userdata($userdata);
        $this->session->sess_destroy();
        redirect(base_url().'auth/index');
    }

}
