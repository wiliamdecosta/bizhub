<?php

/**
 * Permissions Model
 *
 */
class Permissions extends Abstract_model {

    public $table           = "permissions";
    public $pkey            = "permission_id";
    public $alias           = "prms";

    public $fields          = array(
                                'permission_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Permissions'),
                                'permission_name'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Permission Name'),
                                'permission_display_name'   => array('nullable' => true, 'type' => 'str', 'unique' => true, 'display' => 'Display Name'),
                                'permission_description'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Description'),

                                'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "prms.*";
    public $fromClause      = "permissions prms";

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
            $this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata['user_name'];
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];

            $this->record[$this->pkey] = $this->generate_id($this->table, $this->pkey);

        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
            //if false please throw new Exception
        }
        return true;
    }

}

/* End of file Permissions.php */