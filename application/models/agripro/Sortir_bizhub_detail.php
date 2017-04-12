<?php

/**
 * Raw Material Model
 *
 */
class Sortir_bizhub_detail extends Abstract_model {

    public $table           = "sortir_bizhub_detail";
    public $pkey            = "sortir_bizhub_det_id";
    public $alias           = "sort_det";

    public $fields          = array(
                                'sortir_bizhub_det_id'   => array('pkey' => true, 'type' => 'int', 'nullable' => false, 'unique' => true, 'display' => 'ID Sortir'),
                                'product_id'             => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'sortir_bizhub_id'       => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Sortir ID'),
                                'sortir_bizhub_det_qty'  => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'QTY'),
                                'sortir_bizhub_det_qty_init'  => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'QTY')
                            );

    public $selectClause    = "sort_det.sortir_bizhub_det_id, sort_det.sortir_bizhub_id, sort_det.product_id,
                                sort_det.sortir_bizhub_det_qty, sort_det.sortir_bizhub_det_qty_init,
                                sr.sortir_bizhub_tgl,
                                pr.product_name, pr.product_code";
    public $fromClause      = " sortir_bizhub_detail as sort_det
                                left join sortir_bizhub sr on sort_det.sortir_bizhub_id = sr.sortir_bizhub_id
							    left join product pr on sort_det.product_id = pr.product_id
								";

    public $refs            = array("packing_bizhub_detail" => "sortir_bizhub_det_id");

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
            $this->record['sortir_bizhub_det_qty_init'] = $this->record['sortir_bizhub_det_qty'];
        }else {
            //do something
            //example:
            $this->record['sortir_bizhub_det_qty_init'] = $this->record['sortir_bizhub_det_qty'];
            //if false please throw new Exception
        }
        return true;
    }

    function get_sm_id($sortir_bizhub_det_id){

         $sql = " SELECT distinct coalesce(in_biz_det_id,production_bizhub_id) ref_id,
                    CASE WHEN in_biz_det_id IS NULL THEN 'PRODUCTION' ELSE 'MATERIAL' END as type
                    FROM sortir_bizhub
                        WHERE sortir_bizhub_id = ( SELECT sortir_bizhub_id
                                                FROM sortir_bizhub_detail
                                                    WHERE sortir_bizhub_det_id = $sortir_bizhub_det_id limit 1 ) ";
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

        $ci->load->model('agripro/sortir_bizhub');
        $srt = $ci->sortir_bizhub;

        $ci->load->model('agripro/incoming_goods_detail');
        $tsm = $ci->incoming_goods_detail;

        $ci->load->model('agripro/production_bizhub');
        $tProduction = $ci->production_bizhub;

        //  get ref id sm_id / production_id
        $ref = $this->get_sm_id($sortir_detail['sortir_bizhub_det_id']);
        $ref_id = $ref['ref_id'];
        $type = $ref['type'];

        // get data sortir
        $srt->setCriteria('sortir_bizhub_id = '.$sortir_detail['sortir_bizhub_id'] );
        $datasrt = $srt->getAll();
        foreach($datasrt as $datasrts) {
            $sdate = $datasrts['sortir_bizhub_tgl'];
            }
        //$whid = 0;
        if($type == 'PRODUCTION'){
            // ref id = production_id
            $tProduction->setCriteria('a.production_bizhub_id = '.$ref_id);
            $dataprd = $tProduction->getAll();
            foreach($dataprd as $dataprds) {
                $whid = $dataprds['warehouse_id'];
                $prd_id = $dataprds['product_id'];
            }


            $sql = "UPDATE production_bizhub
                    SET production_bizhub_qty = production_bizhub_qty - ".$sortir_detail['sortir_bizhub_det_qty']."
                        WHERE production_bizhub_id = ".$ref_id;

            $stock_refcode = 'PRODUCTION_SORTIR_BIZHUB';

        }else{
            $tsm->setCriteria(' in_biz_det_id = '.$ref_id);
            $datatsm = $tsm->getAll();
            foreach($datatsm as $datatsms) {
                $whid = $datatsms['warehouse_id'];
                $prd_id = $datatsms['product_id'];
                }
            // ref id =  sm_id
            $sql = "UPDATE incoming_bizhub_detail
                    SET qty_netto = qty_netto - ".$sortir_detail['sortir_bizhub_det_qty']."
                        WHERE in_biz_det_id = ".$ref_id;

            $stock_refcode = 'DRYING_SORTIR_BIZHUB';

        }

        $record_stock = array();
        $stock_date = $sdate; //$datasrt['sortir_tgl'];
        $record_stock['stock_tgl_masuk'] = $stock_date; //base on sorting_date
        $record_stock['stock_kg'] = $sortir_detail['sortir_bizhub_det_qty'];
        $record_stock['stock_ref_id'] = $sortir_detail['sortir_bizhub_det_id'];
        $record_stock['stock_ref_code'] = 'SORTIR_STOCK_BIZHUB';
        $record_stock['sc_id'] = $tStockCategory->getIDByCode('SORTIR_STOCK_BIZHUB');
        $record_stock['wh_id'] = $whid;
        $record_stock['product_id'] = $sortir_detail['product_id'];
        $tStock->setRecord($record_stock);
        $tStock->create();

        $record_stock = array();
        $stock_date = $sdate; //$datasrt['sortir_tgl'];
        $record_stock['stock_tgl_keluar'] = $stock_date; //base on sorting_date
        $record_stock['stock_kg'] = $sortir_detail['sortir_bizhub_det_qty'];
        $record_stock['stock_ref_id'] = $sortir_detail['sortir_bizhub_det_id'];
        $record_stock['stock_ref_code'] = $stock_refcode;
        $record_stock['sc_id'] = $tStockCategory->getIDByCode('DRYING_STOCK_BIZHUB');
        $record_stock['wh_id'] = $whid;
        $record_stock['product_id'] = $prd_id;
        $record_stock['stock_description'] = 'sm_qty_bersih has used by sortir_detail';
        $tStock->setRecord($record_stock);
        $tStock->create();

        $tsm->db->query($sql);

    }


    public function removeStock($sortir_bizhub_det_id) {

        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;

        $ci->load->model('agripro/sortir_bizhub_detail');
        $tSortirDetail = $ci->sortir_detail;

        $ci->load->model('agripro/stock_material');
        $tStockMaterial = $ci->stock_material;

        /**
            Step Delete Stock Sortir detail
            1. delete sortir detail
            2. update stock_material qty_bersih + deleted qty_sortir
        **/

        // get qty detail
        $tSortirDetail->setCriteria('sortir_bizhub_det_id = '.$sortir_bizhub_det_id);
        $datasrt = $tSortirDetail->getAll();

        $qty_detail = 0;
        foreach($datasrt as $datasrts) {
            $qty_detail = $datasrts['sortir_bizhub_det_qty'];
        }

        $ref = $this->get_sm_id($sortir_bizhub_det_id);
        $ref_id = $ref['ref_id'];
        $type = $ref['type'];

        if($type == 'PRODUCTION'){
            // ref id = production_id

            $sql = "UPDATE production_bizhub
                    SET production_bizhub_qty = production_bizhub_qty + ".$qty_detail."
                        WHERE production_bizhub_id = ".$ref_id;

            $stock_refcode = 'PRODUCTION_SORTIR_BIZHUB';

        }else{
            // ref id =  sm_id
            $sql = "UPDATE incoming_bizhub_detail
                    SET qty_netto = qty_netto + ".$qty_detail."
                        WHERE sm_id = ".$ref_id;

            $stock_refcode = 'DRYING_SORTIR_BIZHUB';

        }


        $tStock->deleteByReference($sortir_detail_id, 'SORTIR_STOCK_BIZHUB');
        $tStock->deleteByReference($sortir_detail_id, $stock_refcode);

        $tStock->db->query($sql);
    }

}

/* End of file Groups.php */