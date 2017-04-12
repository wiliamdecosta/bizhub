<?php

/**
 * Product Model
 *
 */
class Product extends Abstract_model
{

    public $table = "product";
    public $pkey = "product_id";
    public $alias = "prod";

    public $fields = array(
        'product_id' => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'Product Id'),
        'product_code' => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Product Code'),
        'product_name' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Name'),
        'parent_id' => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Parent Product'),
        'product_category_id' => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Product Category'),
        'product_description' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Description')

    );

    public $selectClause = "prod.product_id, prod.parent_id,prod.product_name,prod.product_description,prod.product_category_id,
                               prod.product_code, par.parent_name ";
    public $fromClause = "product prod
                               LEFT JOIN (select product_id as parent_id, product_name as parent_name from product ) as par
                                    ON par.parent_id = prod.parent_id ";

    public $refs = array('product' => 'parent_id');

    function __construct()
    {
        parent::__construct();
    }

    function validate()
    {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if ($this->actionType == 'CREATE') {
            //do something
            // example :
            /* $this->record['created_date'] = date('Y-m-d');
             $this->record['created_by'] = $userdata['user_name'];
             $this->record['updated_date'] = date('Y-m-d');
             $this->record['updated_by'] = $userdata['user_name'];*/
            if ($this->record['parent_id'] == '')
                $this->record['parent_id'] = null;
        } else {
            //do something
            //example:
            /* $this->record['updated_date'] = date('Y-m-d');
             $this->record['updated_by'] = $userdata['user_name'];*/
            //if false please throw new Exception
        }
        return true;
    }

}

/* End of file Groups.php */