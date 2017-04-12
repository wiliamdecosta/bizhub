<?php

/**
 * Role Module Model
 *
 */
class Role_module extends Abstract_model {

    public $table           = "role_module";
    public $pkey            = "";
    public $alias           = "rm";

    public $fields          = array(
                                'module_id'     => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Module ID'),
                                'role_id'    => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Role ID'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "(rm.role_id || '.' || rm.module_id) AS id,
                                rm.module_id, rm.role_id, m.module_name";
    public $fromClause      = "role_module rm
                               left join modules m on rm.module_id = m.module_id";

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
        $role_id = $code[0];
        $module_id = $code[1];

        $sql = "delete from role_module where role_id = ? and module_id = ?";
        $this->db->query($sql, array($role_id, $module_id));
        return true;
    }

}

/* End of file Groups.php */