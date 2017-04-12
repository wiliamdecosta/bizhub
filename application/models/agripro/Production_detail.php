<?php

/**
 * Farmer Model
 *
 */
class Production_detail extends Abstract_model {

    public $table           = "production_detail";
    public $pkey            = "production_detail_id";
    public $alias           = "pd";

    public $fields          = array(
                                'production_detail_id'            => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Stock Material'),
                                'production_id'                   => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'SM ID'),
                                'sm_id'                           => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'RM ID'),
                               // 'product_id'                           => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'production_detail_qty'           => array('nullable' => false, 'type' => 'float', 'unique' => false, 'display' => 'Qty'),

                            );

    public $selectClause    = " pd.production_detail_id,pd.production_id,pd.sm_id,pd.production_detail_qty,pd.description,
                                 prd.product_name,
                                 fm.fm_name,
                                 prd.product_id,
                                 sm.sm_no_trans
                                ";

    public $fromClause      = " production_detail pd
                                inner join stock_material sm ON pd.sm_id = sm.sm_id
                                inner join product prd ON sm.product_id = prd.product_id
                                inner join farmer fm ON sm.fm_id = fm.fm_id
                                ";

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

            /*$this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata->username;
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/

        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;
        }
        return true;
    }



}

/* End of file Groups.php */