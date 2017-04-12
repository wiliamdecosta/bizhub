<?php

/**
 * Role Menu Model
 *
 */
class Role_menu extends Abstract_model {

    public $table           = "role_menu";
    public $pkey            = "";
    public $alias           = "rm";

    public $fields          = array(
                                'menu_id'     => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Menu ID'),
                                'role_id'    => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Role ID'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "(rm.role_id || '.' || rm.menu_id) AS id,
                                rm.menu_id, rm.role_id, m.menu_title, m.menu_url, m.menu_icon, m.menu_order";
    public $fromClause      = "role_menu rm
                               left join menus m on rm.menu_id = m.menu_id";

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

    public function getItem($role_id, $menu_id) {
        $sql = "select * from role_menu where role_id = ? and menu_id = ?";
        $query = $this->db->query($sql, array($role_id, $menu_id));

        $result = $query->row();
        return $result;
    }

    public function deleteItem($role_id, $menu_id) {
        $sql = "delete from role_menu where role_id = ? and menu_id = ?";
        $this->db->query($sql, array($role_id, $menu_id));

    }

}

/* End of file Role_menu.php */