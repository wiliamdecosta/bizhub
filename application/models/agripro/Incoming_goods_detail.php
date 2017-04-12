<?php

/**
 * Shipping Model
 *
 */
class Incoming_goods_detail extends Abstract_model {

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


    public $selectClause    = "incd.*, inc.in_biz_id, inc.in_biz_date, prd.product_code,prd.product_id, inc.in_shipping_id, inc.warehouse_id ";
    public $fromClause      = " (select incd.*, case when (select count(*)
            							from production_bizhub_detail
                                        where in_biz_det_id = incd.in_biz_det_id) > 0 then
                                        'Production'
                                        when (select count(*)
            							from sortir_bizhub
                                        where in_biz_det_id = incd.in_biz_det_id) > 0 then
                                        'Selection'
                                        else
                                        ''
                                        end used_by from incoming_bizhub_detail incd ) incd
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
            $this->record['qty_netto_init'] = $this->record['qty_netto'];

            //$this->record['pkg_serial_number'] = $this->getSerialNumber();
            //$this->record['pkg_batch_number'] = $this->getBatchNumber($this->record['pkg_serial_number'] );
        }else {
            //do something
            //example:
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata['user_name'];
			$this->record['qty_netto_init'] = $this->record['qty_netto'];
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

	function checkIsUsed($in_biz_id){

		 $sql = " SELECT
			        	(select count(*)
			        		from production_bizhub_detail
			            	where in_biz_det_id  in (select distinct in_biz_det_id
			            							from incoming_bizhub_detail
			            							where in_biz_id = '".$in_biz_id."' )
			            ) +
			            (select count(*)
			            	from sortir_bizhub
			            where in_biz_det_id in (select distinct in_biz_det_id
			            							from incoming_bizhub_detail
			            							where in_biz_id = '".$in_biz_id."' )
			            ) is_exist
			            ";

        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();
		return $row['is_exist'];
	}


	function checkIsUsedDet($in_biz_det_id){

		 $sql = " SELECT
			        	(select count(*)
			        		from production_bizhub_detail
			            	where in_biz_det_id = '".$in_biz_det_id."'
			            ) +
			            (select count(*)
			            	from sortir_bizhub
			            where in_biz_det_id = '".$in_biz_det_id."'
			            ) is_exist
			            ";

        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();
		return $row['is_exist'];
	}

      function insertStock($in_biz_det_id, $val) {
        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

		// data incoming goods detail
			$this->setCriteria('in_biz_det_id = '.$in_biz_det_id );
			$tIncoming_goods_detail = $this->getAll();
			foreach($tIncoming_goods_detail as $dataincd) {
				$sdate = $dataincd['updated_date'];
				$qtyRescale = $dataincd['qty_rescale'];
				$product_id = $dataincd['in_product_id'];
				$packing_id = $dataincd['in_packing_id'];
				}

		if($val == 'L'){
			$sql = "DELETE FROM stock
				WHERE stock_ref_code = 'RAW_MATERIAL_STOCK_BIZHUB'
				AND stock_ref_id = ?
				AND wh_id = ?
				";
			$this->db->query($sql, array($packing_id, $this->session->userdata('wh_id')));
		}else{


			$sql = "DELETE FROM stock
				WHERE stock_ref_code = 'RAW_MATERIAL_STOCK_BIZHUB'
				AND stock_ref_id = ?
				AND wh_id = ?
				";
			$this->db->query($sql, array($packing_id, $this->session->userdata('wh_id')));

			$record_stock = array();
			$stock_date = $sdate; //$datasrt['sortir_tgl'];
			$record_stock['stock_tgl_masuk'] = $stock_date; //base on sorting_date
			$record_stock['stock_kg'] = $qtyRescale;
			$record_stock['stock_ref_id'] = $packing_id;
			$record_stock['stock_ref_code'] = 'RAW_MATERIAL_STOCK_BIZHUB';
			$record_stock['sc_id'] = $tStockCategory->getIDByCode('RAW_MATERIAL_STOCK_BIZHUB');
			$record_stock['wh_id'] = $this->session->userdata('wh_id');
			$record_stock['product_id'] = $product_id;
			$tStock->setRecord($record_stock);
			$tStock->create();

		}


		/* $record_stock = array();
        $stock_date = $sdate; //$datasrt['sortir_tgl'];
        $record_stock['stock_tgl_keluar'] = $stock_date; //base on sorting_date
        $record_stock['stock_kg'] = $sortir_detail['sortir_detail_qty'];
        $record_stock['stock_ref_id'] = $sortir_detail['sortir_detail_id'];
        $record_stock['stock_ref_code'] = $stock_refcode;
        $record_stock['sc_id'] = $tStockCategory->getIDByCode('DRYING_STOCK');
        $record_stock['wh_id'] = $whid;
        $record_stock['product_id'] = $prd_id;
        $record_stock['stock_description'] = 'sm_qty_bersih has used by sortir_detail';
        $tStock->setRecord($record_stock);
        $tStock->create(); */


    }

	public function delete_by_biz_id($in_biz_id){

		$sql = "DELETE FROM incoming_bizhub_detail
				WHERE in_biz_id = ?
		";
		$this->db->query($sql, array($in_biz_id));

		$sql = "DELETE FROM stock
				WHERE stock_ref_code = 'RAW_MATERIAL_STOCK_BIZHUB'
				AND stock_ref_id in (SELECT distinct in_biz_det_id
										FROM incoming_bizhub_detail
											WHERE in_biz_id = ? )
				AND wh_id = ?
				";
		$this->db->query($sql, array($in_biz_id, $this->session->userdata('wh_id')));
		return true;
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


		$tStock->deleteByReference($sortir_detail_id, 'SORTIR_STOCK');
		$tStock->deleteByReference($sortir_detail_id, $stock_refcode);

        $tStock->db->query($sql);
	}
}
/* End of file Shipping.php */