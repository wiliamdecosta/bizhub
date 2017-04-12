<?php

/**
 * Farmer Model
 *
 */
class Farmer extends Abstract_model {

    public $table           = "farmer";
    public $pkey            = "fm_id";
    public $alias           = "fm";

    public $fields          = array(
                                'fm_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Farmer'),
                                'prov_id'            => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Prov ID'),
                                'kota_id'            => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Kota ID'),
                                'fm_code'           => array('nullable' => true, 'type' => 'str', 'unique' => true, 'display' => 'Kode'),
                                'fm_name'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Nama'),
                                'fm_jk'             => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Jenis Kelamin'),
                                'fm_address'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Alamat'),
                                'fm_no_sertifikasi' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Nomor Sertifikasi'),
                                'fm_no_hp'          => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Nomor HP'),
                                'fm_email'          => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Email'),
                                'fm_tgl_lahir'      => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tgl Lahir'),

                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );

    public $selectClause    = "fm.fm_id, fm.fm_code, fm.fm_name, fm.fm_jk,
                                    fm.fm_address, fm.fm_no_sertifikasi, fm.fm_no_hp,
                                    fm.fm_email, to_char(fm.fm_tgl_lahir,'yyyy-mm-dd') as fm_tgl_lahir,
                                    fm.prov_id, fm.kota_id,
                                    prov.prov_code, kota.kota_name,
                                    to_char(fm.created_date,'yyyy-mm-dd') as created_date, fm.created_by,
                                    to_char(fm.updated_date,'yyyy-mm-dd') as updated_date, fm.updated_by,
                                    ";
    public $fromClause      = "farmer fm
                                left join provinsi as prov on fm.prov_id = prov.prov_id
                                left join kota as kota on fm.kota_id = kota.kota_id";

    public $refs            = array('plantation' => 'fm_id');

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

            if(empty($this->record['fm_tgl_lahir']))
                unset($this->record['fm_tgl_lahir']);

            if(empty($this->record['kota_id']))
                unset($this->record['kota_id']);

            if(empty($this->record['prov_id']))
                unset($this->record['prov_id']);
        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
            //if false please throw new Exception
            if(empty($this->record['fm_tgl_lahir'])) {
                $this->db->set('fm_tgl_lahir',null,false);
                unset($this->record['fm_tgl_lahir']);
            }

            if(empty($this->record['kota_id'])) {
                $this->db->set('kota_id',null,false);
                unset($this->record['kota_id']);
            }

            if(empty($this->record['prov_id'])) {
                $this->db->set('prov_id',null,false);
                unset($this->record['prov_id']);
            }
        }
        return true;
    }

}

/* End of file Groups.php */