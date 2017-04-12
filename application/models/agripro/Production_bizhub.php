<?php

/**
 * Raw Material Model
 *
 */
class Production_bizhub extends Abstract_model
{

    public $table = "production_bizhub";
    public $pkey = "production_bizhub_id";
    public $alias = "a";

    public $fields = array(
        'production_bizhub_id' => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Packaging'),
        'product_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
        'warehouse_id' => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'WH ID'),
        // 'product_category_id'   => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product Category ID'),
        'production_bizhub_code' => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Production Code'),
        'production_bizhub_date' => array('nullable' => false, 'type' => 'date', 'unique' => false, 'display' => 'Production Date'),
        'production_bizhub_qty' => array('nullable' => false, 'type' => 'float', 'unique' => false, 'display' => 'Production Quantity'),
        'production_bizhub_qty_init' => array('nullable' => false, 'type' => 'float', 'unique' => false, 'display' => 'Production Quantity'),

        /* 'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
         'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
         'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
         'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),*/

    );


    public $selectClause = "a.production_bizhub_id,
                            a.production_bizhub_code,
                            to_char(a.production_bizhub_date,'yyyy-mm-dd') as production_bizhub_date,
                            a.production_bizhub_qty,
                            b.product_id,
                            b.product_name,
                            b.product_code,
                            b.product_category_id,
                            a.warehouse_id,
                            a.production_bizhub_qty_init
                                ";
    public $fromClause = "production_bizhub a
                                left join product b
                                on a.product_id = b.product_id";

    public $refs = array();

    function __construct()
    {
        parent::__construct();
    }

    function validate()
    {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if ($this->actionType == 'CREATE') {

            /*$this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata->username;
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/

            //$this->record['pkg_serial_number'] = $this->getSerialNumber();
            //$this->record['pkg_batch_number'] = $this->getBatchNumber($this->record['pkg_serial_number'] );
        } else {
            //do something
            //example:
            /*$this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/
            //if false please throw new Exception
        }
        return true;
    }

    function genProductionCode()
    {

        $sql = "select max(substring(production_bizhub_code, 5 )) as total from production_bizhub
                    where to_char(production_bizhub_date,'yyyymm') = '" . substr(str_replace('-', '', $this->record['production_bizhub_date']), 0, 6) . "'";

        $query = $this->db->query($sql);

        $row = $query->row_array();
        if (empty($row)) {
            $row = array('total' => 0);
        }

        $production_code = substr(str_replace('-', '', $this->record['production_bizhub_date']), 2, 4) . "" . str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
        return $production_code;
    }

    function insertStock($prod)
    {
        $ci = &get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $ci->load->model('agripro/production_detail_bizhub');
        $prod_det = $ci->production_detail_bizhub;

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

        $prod_det->setCriteria('pd.production_bizhub_id = ' . $prod['production_bizhub_id']);
        $details = $prod_det->getAll();

        try{

            foreach ($details as $production_detail) {
                $record = array();
                $record['stock_tgl_keluar'] = $prod['production_bizhub_date'];
                $record['stock_kg'] = $production_detail['production_bizhub_det_qty'];
                $record['stock_ref_id'] = $production_detail['in_biz_det_id'];
                $record['stock_ref_code'] = 'DRYING_OUT_BIZHUB';
                $record['sc_id'] = $tStockCategory->getIDByCode('DRYING_STOCK_BIZHUB');
                $record['wh_id'] = $prod['warehouse_id'];
                $record['product_id'] = $production_detail['product_id'];
                $record['stock_description'] = 'Drying Stock has used for Production Detail';
                $tStock->setRecord($record);
                $tStock->create();
            }


            ######################################
            ### Update Raw Material Qty (Decrease)
            ######################################

            foreach ($details as $pd_det) {

                $decrease_kg = (float)$pd_det['production_bizhub_det_qty'];
                $sql = "UPDATE incoming_bizhub_detail SET
                          qty_netto = qty_netto - " . $decrease_kg . "
                            WHERE in_biz_det_id = " . $pd_det['in_biz_det_id'];
                $prod_det->db->query($sql);
            }
            return 'OK';
        }catch(Exception $e){
            return $e->getMessage();
        }

    }

    public function removeProduction($production_id)
    {

        $ci = &get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;

        $ci->load->model('agripro/production_detail_bizhub');
        $tProdDetail = $ci->production_detail_bizhub;

        $ci->load->model('agripro/incoming_goods_detail');
        $tSM = $ci->incoming_goods_detail;

        /**
         * Steps to Delete Production
         * 0 . Remove Stock Production
         * 1. Remove all stock_detail first
         * 2. When loop for delete stock_detail, save data production in array
         * 3. Delete data production in stock
         * 4. Loop data drying for delete data drying in stock and restore qty to drying
         * 5. Delete data master production
         */
         $tStock->deleteByReference($production_id, 'PRODUCTION_IN_BIZHUB');


        $data_drying = array();
        $tProdDetail->setCriteria('pd.production_bizhub_id = ' . $production_id);
        $details = $tProdDetail->getAll();

        $loop = 0;
        foreach ($details as $prd_detail) {
            $data_drying[$loop]['in_biz_det_id'] = $prd_detail['in_biz_det_id'];
            $data_drying[$loop]['qty_netto'] = $prd_detail['production_bizhub_det_qty'];
            $loop++;

            $tProdDetail->remove($prd_detail['production_bizhub_det_id']);
        }


        /**
         * loop for delete data stock by sortir_id and restore store_qty in table sortir
         */
        foreach ($data_drying as $drying) {
            //delete data stock by in_biz_det_id
            $tStock->deleteByReference($drying['in_biz_det_id'], 'DRYING_OUT_BIZHUB');

            //restore store qty
            $increase_kg = (float)$drying['qty_netto'];
            $sql = "UPDATE incoming_bizhub_detail SET qty_netto = qty_netto + " . $increase_kg . "
                        WHERE in_biz_det_id = " . $drying['in_biz_det_id'];

            $tSM->db->query($sql);

        }

        /**
         * Delete data master packing
         */
        $this->remove($production_id);
    }

    public function checkQtyUsedBySortir($item)
    {
        $this->db->where('production_id', $item);
        $query = $this->db->get('sortir');

        return $query->num_rows();
    }

    public function InsertStockMaster($row){

       // print_r($record['production_date']);
        //exit;
       $ci = &get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

        $record = array();
        $record['stock_tgl_masuk'] = $row['production_bizhub_date'];
        $record['stock_kg'] = $row['production_bizhub_qty'];
        $record['stock_ref_id'] = $row['production_bizhub_id'];
        $record['stock_ref_code'] = 'PRODUCTION_IN_BIZHUB';
        $record['sc_id'] = $tStockCategory->getIDByCode('PRODUCTION_STOCK_BIZHUB');
        $record['wh_id'] = $row['warehouse_id'];
        $record['product_id'] = $row['product_id'];
        $record['stock_description'] = 'Insert Stock Production . Ref Production ID : '.$row["production_bizhub_id"];
        $tStock->setRecord($record);
        $tStock->create();
    }

}
/* End of file Groups.php */