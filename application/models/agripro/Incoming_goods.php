<?php

/**
 * Shipping Model
 *
 */
class Incoming_goods extends Abstract_model {

    public $table           = "incoming_bizhub";
    public $pkey            = "in_biz_id";
    public $alias           = "inc";

    public $fields          = array(
                                'in_biz_id'           => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Packaging'),
                                'in_biz_date'         => array('nullable' => false, 'type' => 'date', 'unique' => false, 'display' => 'Shipping Date'),
                                'in_shipping_id'  => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Driver Name'),
                                'warehouse_id'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Notes'),

                                'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );


    public $selectClause    = "inc.*, wh.wh_name, sh.shipping_date, sh.shipping_driver_name, sh.shipping_notes,
                                         (select wh_name
                                        from warehouse
                                            where wh_id = (select distinct wh_id
                                                                from packing
                                                                    where packing_id in (select packing_id
                                                                            from shipping_detail
                                                                            where shipping_id = inc.in_shipping_id))
                                                                            limit 1)wh_shipping_name
                                ";
    public $fromClause      = "incoming_bizhub inc
								join warehouse wh on wh.wh_id = inc.warehouse_id
								join shipping sh on inc.in_shipping_id = sh.shipping_id

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

            //$this->record['pkg_serial_number'] = $this->getSerialNumber();
            //$this->record['pkg_batch_number'] = $this->getBatchNumber($this->record['pkg_serial_number'] );
        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
            //if false please throw new Exception
        }
        return true;
    }

	function create_details($in_biz_id){

		$sql = "INSERT INTO incoming_bizhub_detail (in_biz_det_id ,
													  in_biz_id ,
													  in_packing_id ,
													  packing_batch_number ,
													  in_product_id ,
													  qty_source,
													  in_biz_det_status ,
													  created_date )
				SELECT nextval('incoming_bizhub_detail_in_biz_det_id_seq'::regclass),
					   $in_biz_id,
					   pkg.packing_id,
					   pkg.packing_batch_number,
					   pkg.product_id,
					   pkg.packing_kg,
					   'E',
					   current_date
					FROM incoming_bizhub i
							join shipping_detail sh on i.in_shipping_id = sh.shipping_id
							join packing pkg on sh.packing_id = pkg.packing_id
					WHERE in_biz_id = $in_biz_id

				";
        $this->db->query($sql, array($in_biz_id));


	}

	function get_pkg_info_by_id($packing_id){

		$sql = "select a.*, b.product_code
					from packing a
						join product b on a.product_id = b.product_id
					where packing_id = ?";

        $query = $this->db->query($sql, array($packing_id));
        $row = $query->row_array();

        return $row;

	}

	function update_detail($col, $val, $id, $col_id) {
        $sql = "update incoming_bizhub_detail
					set ? = ?
					where ? = ? ";
        $this->db->query($sql, array($col, $val, $col_id, $id));
        return true;
    }

	 function getAllItems() {
        $items = $this->getAll();

        for($record = 0; $record < count($items); $record++) {
            //$items[$record]['shipping_cost'] = $this->getTotalCost($items[$record][$this->pkey]);
        }
        return $items;
    }

    function removeIncoming($in_biz_id){

        $ci = &get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;

        $ci->load->model('agripro/incoming_goods_detail');
        $tProdDetail = $ci->incoming_goods_detail;

        /**
         * Steps to Delete Production
         * 1. Remove detail and stock
         * 2. remove master
         */


        $tProdDetail->setCriteria('incd.in_biz_id = ' . $in_biz_id);
        $details = $tProdDetail->getAll();

        $loop = 0;
        foreach ($details as $prd_detail) {
            $tProdDetail->remove($prd_detail['in_biz_det_id']);
            $tStock->deleteByReference($prd_detail['in_biz_det_id'], 'RAW_MATERIAL_STOCK_BIZHUB');
        }


        /**
         * Delete data master incoming
         */
        $this->remove($in_biz_id);
    }


}
/* End of file Shipping.php */