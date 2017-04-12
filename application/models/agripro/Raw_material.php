<?php

/**
 * Raw Material Model
 *
 */
class Raw_material extends Abstract_model {

    public $table           = "product";
    public $pkey            = "product_id";
    public $alias           = "pr";

    public $fields          = array(
                                'product_id'        => array('pkey' => true, 'type' => 'int', 'nullable' => false, 'unique' => true, 'display' => 'ID Raw Material'),
                                'category_id'       => array('nullable' => false, 'type' => 'int', 'unique' => true, 'display' => 'Category ID'),
                                'product_name'      => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Product Name')

                            );

    public $selectClause    = "pr.product_id,pr.category_id,pr.parent_id,pr.product_name,pr.product_description";
    public $fromClause      = "product pr";

    public $refs            = array();

    function __construct() {
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

        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
            //if false please throw new Exception
        }
        return true;
    }

    public function getListRawMaterial(){
        $sql = "SELECT * FROM product WHERE product_code in ('STICK','KA','KB','KC','KF','KM','KS','KTP') order by product_code asc";
        $q = $this->db->query($sql);
        return $q->result_array();
    }

}

/* End of file Groups.php */