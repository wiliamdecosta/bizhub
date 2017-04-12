<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Panel extends CI_Controller
{

    function __construct() {
        parent::__construct();
        $this->load->model('administration/modules');
    }

    function index() {
        check_login();
        if(!$this->modules->allowAccessPanel($this->session->userdata('user_id'),$this->input->get('module_id'))) {
            redirect(base_url('home'));
            return;
        }

        $this->load->view('panel');
    }

}