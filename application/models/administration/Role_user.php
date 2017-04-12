<?php

/**
 * Groups Model
 *
 */
class Role_user extends Abstract_model {

    public $table           = "role_user";
    public $pkey            = "";
    public $alias           = "ru";

    public $fields          = array(
                                'user_id'     => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'User ID'),
                                'role_id'    => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Role ID'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "(ru.user_id || '.' || ru.role_id) AS id,
                                ru.user_id, ru.role_id, r.role_name";
    public $fromClause      = "role_user ru
                               left join roles r on ru.role_id = r.role_id";

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
        }else {
            //do something
            //example:
            //$this->record['updated_date'] = date('Y-m-d');
            //if false please throw new Exception

            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
        }
        return true;
    }

    public function removeItem($value) {

        $code = explode('.', $value);
        $user_id = $code[0];
        $role_id = $code[1];

        $sql = "delete from role_user where user_id = ? and role_id = ?";
        $this->db->query($sql, array($user_id, $role_id));
        return true;
    }

}

/* End of file Groups.php */