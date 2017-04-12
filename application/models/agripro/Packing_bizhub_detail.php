<?php

/**
 * Farmer Model
 *
 */
class Packing_bizhub_detail extends Abstract_model {

    public $table           = "packing_bizhub_detail";
    public $pkey            = "pd_bizhub_id";
    public $alias           = "pd";

    public $fields          = array(
                                'pd_bizhub_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Packing Detail'),
                                'packing_bizhub_id'        => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'ID Packing'),
                                'sortir_bizhub_det_id'     => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'ID Sortir Detail'),
                                'pd_bizhub_kg'             => array('nullable' => false, 'type' => 'float', 'unique' => false, 'display' => 'Qty Kg'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "pd.*, product.product_id, product.product_code";

    public $fromClause      = "packing_bizhub_detail as pd
                                left join sortir_bizhub_detail on pd.sortir_bizhub_det_id = sortir_bizhub_detail.sortir_bizhub_det_id
                                left join product on sortir_bizhub_detail.product_id = product.product_id
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
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];

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