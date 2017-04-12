<?php

/**
 * Product Model
 *
 */
class Product_detail extends Abstract_model {

    public $table           = "product";
    public $pkey            = "product_id";
    public $alias           = "prod";

    public $fields          = array(
                            'product_id'   => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'Product Id'),
                            'category_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Category'),
                            'product_name' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Name'),
                            'parent_id' => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Parent Product'),
                            'product_description' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Description')

                            );

    public $selectClause    = "prod.product_id, prod.category_id, prod.parent_id,prod.product_name,prod.product_description, cat.category_id,
								cat.category_name, cat.category_description ";
    public $fromClause      = "product prod
							   JOIN category cat ON prod.category_id = cat.category_id ";


    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
           /* $this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata->username;
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/

        }else {
            //do something
            //example:
           /* $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/
            //if false please throw new Exception
        }
        return true;
    }

}

/* End of file Groups.php */