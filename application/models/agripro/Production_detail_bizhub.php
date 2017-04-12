<?php

/**
 * Farmer Model
 *
 */
class Production_detail_bizhub extends Abstract_model {

    public $table           = "production_bizhub_detail";
    public $pkey            = "production_bizhub_det_id";
    public $alias           = "pd";

    public $fields          = array(
    'production_bizhub_det_id' => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID'),
    'production_bizhub_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'SM ID'),
    'in_biz_det_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'RM ID'),
    'production_bizhub_det_qty' => array('nullable' => false, 'type' => 'float', 'unique' => false, 'display' => 'Qty'),
    'description'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),

    'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
    'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
    'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
    'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = " pd.production_bizhub_det_id,
                                pd.production_bizhub_id,
                                pd.in_biz_det_id,
                                pd.production_bizhub_det_qty,
                                pd.description,
                                prd.product_id,
                                prd.product_name,
                                prd.product_code,
                                pkg.packing_batch_number
                                ";

    public $fromClause      = " production_bizhub_detail pd
                                inner join production_bizhub p ON p.production_bizhub_id = pd.production_bizhub_id
                                inner join incoming_bizhub_detail incd ON pd.in_biz_det_id = incd.in_biz_det_id
                                inner join product prd ON p.product_id = prd.product_id
                                inner join packing pkg ON pkg.packing_id = incd.in_packing_id
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

            $this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata['user_name'];
            /*$this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];*/

        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
        }
        return true;
    }

}

/* End of file Groups.php */