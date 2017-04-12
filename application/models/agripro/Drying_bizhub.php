<?php

/**
 * Shipping Model
 *
 */
class Drying_bizhub extends Abstract_model {

    public $table           = "incoming_bizhub_detail";
    public $pkey            = "in_biz_det_id";
    public $alias           = "incd";

    public $fields          = array(
                                'in_biz_det_id'           => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Packaging'),
                                'in_biz_id'         => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'In Biz ID'),
                                'in_packing_id'  => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Driver Name'),
                                'packing_batch_number'  => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Driver Name'),
                                'in_product_id'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'prd id'),
                                'qty_source'        => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'qty source'),
                                'qty_rescale'        => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'qty rescale'),
                                'qty_bruto'        => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'bruto'),
                                'qty_netto'        => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'netto'),
                                'qty_netto_init'        => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'netto'),
								'in_biz_det_status'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
								'in_biz_drying_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Drying Date'),

								'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );


    public $selectClause    = "incd.*, inc.in_biz_id, inc.in_biz_date, prd.product_code, prd.product_id, inc.in_shipping_id, inc.warehouse_id wh_id";
    public $fromClause      = "incoming_bizhub_detail incd
								JOIN incoming_bizhub inc
								ON inc.in_biz_id = incd.in_biz_id
								JOIN product prd
								ON incd.in_product_id = prd.product_id
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
			$this->record['qty_netto'] = $this->record['qty_netto_init'];
            //if false please throw new Exception
        }
        return true;
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


	function InsertStock($record){
        ######################################
        ### 1. Insert Stok DRYING_STOCK (IN)
        ### 2. Insert Stock RAW MATERIAL (Out)
        ### 3. Update QTY NETTO value
        ######################################

        $ci = & get_instance();
        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;


        ################ Step 1 ###############
        $record1 = array();
        $record1['wh_id'] = $record['wh_id'];
        $record1['product_id'] = $record['product_id'];
        $record1['sc_id'] = $tStockCategory->getIDByCode('DRYING_STOCK_BIZHUB');
        $record1['stock_tgl_masuk'] = $record['in_biz_drying_date'];
        $record1['stock_kg'] = $record['qty_rescale'];
        $record1['stock_ref_id'] = $record['in_biz_det_id'];
        $record1['stock_ref_code'] = 'DRYING_IN_BIZHUB';
        $record1['stock_description'] = 'Insert Stock Drying';
        $tStock->setRecord($record1);

        $tStock->create();

        ################ Step 2 ###############
        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $record2 = array();
        $record2['wh_id'] = $record['wh_id'];
        $record2['product_id'] = $record['product_id'];
        $record2['sc_id'] = $tStockCategory->getIDByCode('RAW_MATERIAL_STOCK_BIZHUB');
        $record2['stock_tgl_keluar'] = $record['in_biz_drying_date'];
        $record2['stock_kg'] = $record['qty_rescale'];
        $record2['stock_ref_id'] = $record['in_biz_det_id'];
        $record2['stock_ref_code'] = 'RAW_MATERIAL_OUT_BIZHUB';
        $record2['stock_description'] = 'Raw Material (OUT) for Drying';
        $tStock->setRecord($record2);

        $tStock->create();
    }

	function UpdateStock($rmp) {
        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'UPDATE';

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

        ###################################
        #### Update IN Stock (Drying)
        ###################################
        $record_stock = array();
        $record_stock['stock_tgl_masuk'] = $rmp['in_biz_drying_date'];; //base on packing_tgl
        $record_stock['stock_kg'] = $rmp['qty_netto'];


        $this->db->where(array(
            'stock_ref_id' => $rmp['in_biz_det_id'],
            'stock_ref_code' => 'DRYING_IN_BIZHUB'
        ));
        $this->db->update($tStock->table, $record_stock);

        #####################################
        #### Update OUT Stock (Raw Material)
        #####################################
        $record_stock2 = array();
        $record_stock2['stock_tgl_keluar'] = $rmp['in_biz_drying_date'];; //base on packing_tgl
        $record_stock2['stock_kg'] = $rmp['qty_bruto'];

        $this->db->where(array(
            'stock_ref_id' => $rmp['in_biz_det_id'],
            'stock_ref_code' => 'RAW_MATERIAL_OUT_BIZHUB'
        ));
        $this->db->update($tStock->table, $record_stock2);
    }

	function updateQtyRM($record){
        $this->db->set('qty_bruto',0);
        $this->db->where('in_biz_det_id', $record['in_biz_det_id']);
        $this->db->update('incoming_bizhub_detail');
    }

    function checkDryingQTY($record){

        $this->db->select('qty_netto');
        $this->db->where('in_biz_det_id', $record['in_biz_det_id']);
        $query = $this->db->get('incoming_bizhub_detail');

        return $query->row()->qty_netto;
    }

    function IsStockExist($record){

        $this->db->select('count(*) as jumlah');
        $this->db->where(array(
            'stock_ref_id' => $record['in_biz_det_id'],
            'stock_ref_code' => 'DRYING_IN_BIZHUB'
        ));
        $query = $this->db->get('stock');

        return $query->row()->jumlah;
    }
    function checkQtyUsedProd($record){
        $this->db->where('in_biz_det_id', $record['in_biz_det_id']);
        $query = $this->db->get('production_bizhub_detail');

        return $query->num_rows();
    }

    public function removeStock($sortir_detail_id) {

        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;

        $ci->load->model('agripro/sortir_detail');
        $tSortirDetail = $ci->sortir_detail;

        $ci->load->model('agripro/stock_material');
        $tStockMaterial = $ci->stock_material;

		/**
			Step Delete Stock Sortir detail
			1. delete sortir detail
			2. update stock_material qty_bersih + deleted qty_sortir
		**/

		// get qty detail
		$tSortirDetail->setCriteria('sortir_detail_id = '.$sortir_detail_id);
        $datasrt = $tSortirDetail->getAll();

		$qty_detail = 0;
		foreach($datasrt as $datasrts) {
			$qty_detail = $datasrts['sortir_detail_qty'];
		}

		$ref = $this->get_sm_id($sortir_detail_id);
		$ref_id = $ref['ref_id'];
		$type = $ref['type'];

		if($type == 'PRODUCTION'){
			// ref id = production_id

			$sql = "UPDATE production SET production_qty = production_qty + ".$qty_detail."
                        WHERE production_id = ".$ref_id;

			$stock_refcode = 'PRODUCTION_SORTIR_BIZHUB';

		}else{
			// ref id =  sm_id
			$sql = "UPDATE stock_material SET sm_qty_bersih = sm_qty_bersih + ".$qty_detail."
                        WHERE sm_id = ".$ref_id;

			$stock_refcode = 'DRYING_SORTIR_BIZHUB';

		}


		$tStock->deleteByReference($sortir_detail_id, 'SORTIR_STOCK_BIZHUB');
		$tStock->deleteByReference($sortir_detail_id, $stock_refcode);

        $tStock->db->query($sql);
	}
}
/* End of file Shipping.php */