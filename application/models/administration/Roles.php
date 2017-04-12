<?php

/**
 * Roles Model
 *
 */
class Roles extends Abstract_model {

    public $table           = "roles";
    public $pkey            = "role_id";
    public $alias           = "role";

    public $fields          = array(
                                'role_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Role'),
                                'role_name'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Role Name'),
                                'role_description'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Description'),
                                'is_active'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status'),


                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "role.*, CASE coalesce(role.is_active,'N') WHEN 'N' THEN 'TIDAK AKTIF'
                                        WHEN 'Y' THEN 'AKTIF'
                                    END as status_active";
    public $fromClause      = "roles role";

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

            $this->record[$this->pkey] = $this->generate_id($this->table, $this->pkey);

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

}

/* End of file Groups.php */