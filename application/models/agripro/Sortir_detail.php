<?php

/**
 * Raw Material Model
 *
 */
class Sortir_detail extends Abstract_model {

    public $table           = "sortir_detail";
    public $pkey            = "sortir_detail_id";
    public $alias           = "sort_det";

    public $fields          = array(
                                'sortir_detail_id'       => array('pkey' => true, 'type' => 'int', 'nullable' => false, 'unique' => true, 'display' => 'ID Sortir'),
                                'product_id'             => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'sortir_id'              => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Sortir ID'),
                                'sortir_detail_qty'      => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'QTY'),
                                'sortir_detail_qty_init'      => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'QTY')
                            );

    public $selectClause    = "sort_det.sortir_detail_id, sort_det.sortir_id, sort_det.product_id, sort_det.sortir_detail_qty,sort_det.sortir_detail_qty_init,
                                sr.sortir_tgl, fm.fm_code, fm.fm_name,
                                pr.product_name, pr.product_code";
    public $fromClause      = " sortir_detail as sort_det
                                left join sortir sr on sort_det.sortir_id = sr.sortir_id
								left join stock_material sm on sr.sm_id = sm.sm_id
								left join product pr on sort_det.product_id = pr.product_id
								left join farmer fm on sm.fm_id = fm.fm_id
								";

    public $refs            = array("packing_detail" => "sortir_detail_id");

    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
            $this->record[$this->pkey] = $this->generate_id($this->table,$this->pkey);
            $this->record['sortir_detail_qty_init'] = $this->record['sortir_detail_qty'];
        }else {
            //do something
            //example:
            //if false please throw new Exception
			$this->record['sortir_detail_qty_init'] = $this->record['sortir_detail_qty'];

        }
        return true;
    }

    function get_sortir_id($par){

        $sql = " Select distinct sortir_id from sortir_detail where  ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

        }



    function get_sm_id($sortir_detail_id){

         $sql = " SELECT distinct coalesce(sm_id,production_id) ref_id, CASE WHEN sm_id IS NULL THEN 'PRODUCTION' ELSE 'MATERIAL' END as type
                    FROM sortir
                        WHERE sortir_id = ( SELECT sortir_id
                                                FROM sortir_detail
													WHERE sortir_detail_id = $sortir_detail_id limit 1 ) ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

		$ret = array();
        $ret['ref_id'] = $row['ref_id'];
        $ret['type'] = $row['type'];

		return $ret;
    }

    function insertStock($sortir_detail) {
        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

        $ci->load->model('agripro/packing_detail');
        $tPackingDetail = $ci->packing_detail;

        $ci->load->model('agripro/sortir');
        $srt = $ci->sortir;

        $ci->load->model('agripro/stock_material');
        $tsm = $ci->stock_material;

		$ci->load->model('agripro/production');
        $tProduction = $ci->production;

		// 	get ref id sm_id / production_id
        $ref = $this->get_sm_id($sortir_detail['sortir_detail_id']);
		$ref_id = $ref['ref_id'];
		$type = $ref['type'];

		// get data sortir
        $srt->setCriteria('sortir_id = '.$sortir_detail['sortir_id'] );
        $datasrt = $srt->getAll();
        foreach($datasrt as $datasrts) {
            $sdate = $datasrts['sortir_tgl'];
            }
		//$whid = 0;
		if($type == 'PRODUCTION'){
			// ref id = production_id
			$tProduction->setCriteria('a.production_id = '.$ref_id);
			$dataprd = $tProduction->getAll();
			foreach($dataprd as $dataprds) {
				$whid = $dataprds['warehouse_id'];
				$prd_id = $dataprds['product_id'];
            }


			$sql = "UPDATE production SET production_qty = production_qty - ".$sortir_detail['sortir_detail_qty']."
                        WHERE production_id = ".$ref_id;

			$stock_refcode = 'PRODUCTION_SORTIR';

		}else{
			$tsm->setCriteria('sm.sm_id = '.$ref_id);
			$datatsm = $tsm->getAll();
			foreach($datatsm as $datatsms) {
				$whid = $datatsms['wh_id'];
				$prd_id = $datatsms['product_id'];
				}
			// ref id =  sm_id
			$sql = "UPDATE stock_material SET sm_qty_bersih = sm_qty_bersih - ".$sortir_detail['sortir_detail_qty']."
                        WHERE sm_id = ".$ref_id;

			$stock_refcode = 'DRYING_SORTIR';

		}

        $record_stock = array();
        $stock_date = $sdate; //$datasrt['sortir_tgl'];
        $record_stock['stock_tgl_masuk'] = $stock_date; //base on sorting_date
        $record_stock['stock_kg'] = $sortir_detail['sortir_detail_qty'];
        $record_stock['stock_ref_id'] = $sortir_detail['sortir_detail_id'];
        $record_stock['stock_ref_code'] = 'SORTIR_STOCK';
        $record_stock['sc_id'] = $tStockCategory->getIDByCode('SORTIR_STOCK');
        $record_stock['wh_id'] = $whid;
        $record_stock['product_id'] = $sortir_detail['product_id'];
        $tStock->setRecord($record_stock);
        $tStock->create();

		$record_stock = array();
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
        $tStock->create();

		$tsm->db->query($sql);

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

			$stock_refcode = 'PRODUCTION_SORTIR';

		}else{
			// ref id =  sm_id
			$sql = "UPDATE stock_material SET sm_qty_bersih = sm_qty_bersih + ".$qty_detail."
                        WHERE sm_id = ".$ref_id;

			$stock_refcode = 'DRYING_SORTIR';

		}


		$tStock->deleteByReference($sortir_detail_id, 'SORTIR_STOCK');
		$tStock->deleteByReference($sortir_detail_id, $stock_refcode);

        $tStock->db->query($sql);
	}
}