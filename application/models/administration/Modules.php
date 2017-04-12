<?php

/**
 * Modules Model
 *
 */
class Modules extends Abstract_model {

    public $table           = "modules";
    public $pkey            = "module_id";
    public $alias           = "mod";

    public $fields          = array(
                                'module_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Module'),
                                'module_name'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Module Name'),
                                'module_description'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Module Description'),
                                'module_icon'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Icon'),
                                'is_active'             => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status Active'),

                                'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "mod.*, CASE coalesce(mod.is_active,'N') WHEN 'N' THEN 'TIDAK AKTIF'
                                        WHEN 'Y' THEN 'AKTIF'
                                    END as status_active";
    public $fromClause      = "modules mod";

    public $refs            = array('menus' => 'module_id',
                                    'role_module' => 'module_id');

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

    function getHomeModules($user_id) {
        $sql = "select m.module_id, m.module_name, m.module_icon, m.module_description
                        from modules m
                        where m.is_active = 'Y' and m.module_id in (
                        select rm.module_id from role_module rm
                        where rm.role_id in (select c.role_id
                                    from role_user c
                                    left join roles d on c.role_id = d.role_id
                                    where c.user_id = ? and d.is_active = 'Y'))";


        $query = $this->db->query($sql, array($user_id));
        $rows = $query->result_array();

        return $rows;
    }

    function allowAccessPanel($user_id, $module_id) {
        if(empty($user_id) or empty($module_id)) return false;
        $sql = "select m.module_id, m.module_name, m.module_icon, m.module_description
                        from modules m
                        where m.is_active = 'Y' and m.module_id in (
                        select rm.module_id from role_module rm
                        where rm.role_id in (select c.role_id
                                    from role_user c
                                    left join roles d on c.role_id = d.role_id
                                    where c.user_id = ? and d.is_active = 'Y'))
                and m.module_id = ?";

        $query = $this->db->query($sql, array($user_id, $module_id));
        $rows = $query->row_array();

        return $rows != null;
    }

}

/* End of file Groups.php */