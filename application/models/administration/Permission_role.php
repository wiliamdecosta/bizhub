<?php

/**
 * Permission_role Model
 *
 */
class Permission_role extends Abstract_model {

    public $table           = "permission_role";
    public $pkey            = "";
    public $alias           = "pr";
	public $role_id 		= '';
    public $fields          = array(
                                'permission_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Permission ID'),
                                'role_id'    	=> array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Role ID'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "(coalesce(pr.role_id,0) || '.' || permissions.permission_id) AS role_permissions_id, permissions.permission_name, permissions.permission_display_name,
                                CASE coalesce(pr.role_id,0)
                                    WHEN 0 THEN 'No'
                                    ELSE 'Yes'
                                END AS status_permission";
    public $fromClause      = "permissions
							   left join permission_role pr on permissions.permission_id = pr.permission_id %s";

    public $refs            = array();

    function __construct($role_id = ''){
		if (!empty($role_id)){
			$this->role_id = (int) $role_id;
			$this->fromClause = sprintf($this->fromClause, 'and pr.role_id = '.$this->role_id);
		}else{
			$this->fromClause = sprintf($this->fromClause, '');
		}

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


    function removeItem($role_id, $permission_id) {
        $sql = "delete from permission_role where role_id = ? and permission_id = ?";
        $this->db->query($sql, array($role_id, $permission_id));

        return true;
    }
}

/* End of file Groups.php */