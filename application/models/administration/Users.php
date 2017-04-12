<?php

/**
 * Users Model
 *
 */
class Users extends Abstract_model {

    public $table           = "users";
    public $pkey            = "user_id";
    public $alias           = "usr";

    public $fields          = array(
                                'user_id'           => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID User'),
                                'user_name'         => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'User Name'),
                                'user_full_name'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Full Name'),
                                'user_email'        => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Email'),
                                'user_password'     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Password'),
                                'user_status'       => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Status'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "usr.*, CASE coalesce(usr.user_status,'0') WHEN '0' THEN 'Not Active'
                                        WHEN '1' THEN 'Active'
                                    END as status_active";
    public $fromClause      = "users usr";

    public $refs            = array();

    function __construct() {
        $this->db = $this->load->database('bizhub', TRUE);
        $this->db->_escape_char = ' ';
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
            //$this->record['created_date'] = date('Y-m-d');
            //$this->record['updated_date'] = date('Y-m-d');

            $this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata['user_name'];
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];

            if (isset($this->record['user_password'])){
                if (trim($this->record['user_password']) == '') throw new Exception('Password Field is Empty');
                if (strlen($this->record['user_password']) < 4) throw new Exception('Mininum password length is 4 characters');
                $this->record['user_password'] = md5($this->record['user_password']);
            }

            $this->record[$this->pkey] = $this->generate_id($this->table, $this->pkey);

        }else {
            //do something
            //example:
            //$this->record['updated_date'] = date('Y-m-d');
            //if false please throw new Exception
            unset($this->record['user_password']);
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

}

/* End of file Users.php */