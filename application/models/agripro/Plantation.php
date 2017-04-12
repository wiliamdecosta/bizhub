<?php

/**
 * Raw Material Model
 *
 */
class Plantation extends Abstract_model {

    public $table           = "plantation";
    public $pkey            = "plt_id";
    public $alias           = "plt";

    public $fields          = array(
                                'plt_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Raw Material'),
                                'fm_id'              => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Farmer ID'),
                                'plt_code'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Code'),
                                'plt_luas_lahan'     => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Total Width'),
                                'plt_status'         => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status'),
                                'plt_year_planted'   => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Year Planted'),
                                'plt_date_contract'  => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Contract Date'),
                                'plt_date_registration'  => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Registration Date'),
                                'plt_coordinate'     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Coordinate'),
                                'plt_nama_pemilik'   => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Owner'),
                                'plt_harvest_prediction' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Harvest Prediction'),
                                'plt_inspection_date'  => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Inspection Date'),
                                'plt_inspector'     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Inspector'),
                                'plt_alamat'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Address'),
//                                'plt_harvest_total' => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'Harvest Total'),
                                'created_date'       => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'         => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'       => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'         => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "plt.*, to_char(plt.plt_date_contract,'yyyy-mm-dd') as plt_date_contract,
                                    to_char(plt.plt_date_registration,'yyyy-mm-dd') as plt_date_registration,
                                    to_char(plt.created_date,'yyyy-mm-dd') as created_date,
                                    to_char(plt.updated_date,'yyyy-mm-dd') as updated_date,
                                    fm.fm_code, fm.fm_name";
    public $fromClause      = "plantation plt
                                left join farmer fm on plt.fm_id = fm.fm_id";

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

            if(empty($this->record['plt_date_contract']))
                unset($this->record['plt_date_contract']);
            if(empty($this->record['plt_date_registration']))
                unset($this->record['plt_date_registration']);
            if(empty($this->record['plt_inspection_date']))
                unset($this->record['plt_inspection_date']);
            if(empty($this->record['plt_harvest_prediction']))
                unset($this->record['plt_harvest_prediction']);
        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
            //if false please throw new Exception
            if(empty($this->record['plt_date_contract'])) {
              //  $this->db->set('plt_date_contract',null,false);
                unset($this->record['plt_date_contract']);
            }

            if(empty($this->record['plt_date_registration'])) {
              //  $this->db->set('plt_date_registration',null,false);
                unset($this->record['plt_date_registration']);
            }

            if(empty($this->record['plt_inspection_date'])) {
              //  $this->db->set('plt_inspection_date',null,false);
                unset($this->record['plt_inspection_date']);
            }

            if(empty($this->record['plt_harvest_prediction'])) {
               // $this->db->set('plt_harvest_prediction',null,false);
                unset($this->record['plt_harvest_prediction']);
            }
        }
        return true;
    }

}

/* End of file Groups.php */