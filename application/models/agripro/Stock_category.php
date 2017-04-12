<?php

/**
 * Stock_category Model
 *
 */
class Stock_category extends Abstract_model {

    public $table           = "stock_category";
    public $pkey            = "sc_id";
    public $alias           = "";

    public $fields          = array(
                                'sc_id'             => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Stock Category'),
                                'sc_code'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Code'),
                                'sc_description'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Description'),
                            );

    public $selectClause    = "sc.*";
    public $fromClause      = "stock_category as sc";

    public $refs            = array('stock' => 'sc_id');

    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :

        }else {
            //do something
            //example:
            //if false please throw new Exception
        }
        return true;
    }

    public function getIDByCode($code) {
        if(empty($code)) return "";

        $code = strtoupper($code);
        $sql = "SELECT ".$this->pkey." FROM ".$this->table." WHERE upper(sc_code) = '".$code."'";

        $query = $this->db->query($sql);
        $row = $query->row_array();

        return $row[$this->pkey];
    }

}

/* End of file Groups.php */