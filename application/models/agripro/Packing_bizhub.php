<?php

/**
 * Raw Material Model
 *
 */
class Packing_bizhub extends Abstract_model {

    public $table           = "packing_bizhub";
    public $pkey            = "packing_bizhub_id";
    public $alias           = "pack";

    public $fields          = array(
                                'packing_bizhub_id'            => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Packaging'),
                                'product_id'            => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'warehouse_id'          => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Warehouse ID'),
                                'packing_bizhub_batch_number'  => array('nullable' => true, 'type' => 'str', 'unique' => true, 'display' => 'Batch Number'),
                                'packing_bizhub_serial'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Serial Number'),
                                'packing_bizhub_kg'            => array('nullable' => true, 'type' => 'float', 'unique' => false, 'display' => 'Kg'),
                                'packing_bizhub_date'           => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Packing Date'),
                                'created_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'          => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),

                            );


    public $selectClause    = "pack.*, prod.product_code, prod.product_name, to_char(pack.packing_bizhub_date,'yyyy-mm-dd') as packing_bizhub_date";
    public $fromClause      = "packing_bizhub pack
                                left join product prod
                                on pack.product_id = prod.product_id";

    public $refs            = array('shipping_bizhub_detail' => 'packing_bizhub_id');

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

   function getBatchNumber() {

        $format_serial = 'PRODUCTCODE-DATE-XXXX';

        $sql = "select coalesce(max(substr(packing_bizhub_batch_number, length(packing_bizhub_batch_number)-4 + 1 )::integer),0) as total from packing_bizhub
                    where to_char(packing_bizhub_date,'yyyymmdd') = '".str_replace('-','',$this->record['packing_bizhub_date'])."'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        if(empty($row)) {
            $row = array('total' => 0);
        }

        $ci = & get_instance();

        $format_serial = str_replace('XXXX', str_pad(($row['total']+1), 4, '0', STR_PAD_LEFT), $format_serial);
        $format_serial = str_replace('DATE', str_replace('-','',$this->record['packing_bizhub_date']), $format_serial);

        $ci->load->model('agripro/product');
        $tProduct = $ci->product;

        $itemproduct = $tProduct->get( $this->record['product_id'] );
        $format_serial = str_replace('PRODUCTCODE', $itemproduct['product_code'], $format_serial);

        return array(
            'batch_number' => $format_serial,
            'serial_number' => str_pad(($row['total']+1), 4, '0', STR_PAD_LEFT)
        );
    }

    function insertStock($packing_master) {
        $ci = & get_instance();

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;
        $tStock->actionType = 'CREATE';

        $ci->load->model('agripro/stock_category');
        $tStockCategory = $ci->stock_category;

        $ci->load->model('agripro/packing_bizhub_detail');
        $tPackingDetail = $ci->packing_bizhub_detail;

        /**
         * Steps :
         * 1. Insert master to Stock as PACKING_STOCK category (IN STOCK - PACKING)
         * 2. Insert detail to Stock as SORTIR_STOCK category (OUT STOCK - SORTIR)
         * 3. Decrease Sortir Weight by Detail packing weight
         */


        /**
         * Step 1 - Insert master to stock as PACKING_STOCK (IN STOCK - stock_tgl_masuk)
         */
        $record_stock = array();
        $stock_date = $packing_master['packing_bizhub_date'];
        $record_stock['stock_tgl_masuk'] = $stock_date; //base on packing_tgl
        $record_stock['stock_kg'] = $packing_master['packing_bizhub_kg'];
        $record_stock['stock_ref_id'] = $packing_master['packing_bizhub_id'];
        $record_stock['stock_ref_code'] = 'PACKING_BIZHUB';
        $record_stock['sc_id'] = $tStockCategory->getIDByCode('PACKING_STOCK_BIZHUB');
        $record_stock['wh_id'] = $packing_master['warehouse_id'];
        $record_stock['product_id'] = $packing_master['product_id'];
        $tStock->setRecord($record_stock);
        $tStock->create();

        /**
         * Step 2 - Insert detail to stock as SORTIR_STOCK (OUT STOCK - stock_tgl_keluar)
         */

        $tPackingDetail->setCriteria('pd.packing_bizhub_id = '.$packing_master['packing_bizhub_id']);
        $details = $tPackingDetail->getAll();
        foreach($details as $packing_detail) {
            $record_stock = array();
            $record_stock['stock_tgl_keluar'] = $stock_date; //base on packing_tgl
            $record_stock['stock_kg'] = $packing_detail['pd_bizhub_kg'];
            $record_stock['stock_ref_id'] = $packing_detail['sortir_bizhub_det_id']; //sortir_detail id become reference on stock
            $record_stock['stock_ref_code'] = 'SORTIR_PACKING_BIZHUB';
            $record_stock['sc_id'] = $tStockCategory->getIDByCode('SORTIR_STOCK_BIZHUB');
            $record_stock['wh_id'] = $packing_master['warehouse_id'];
            $record_stock['product_id'] = $packing_detail['product_id'];
            $record_stock['stock_description'] = 'sortir_bizhub_detail_qty has used for packing_bizhub_detail';
            $tStock->setRecord($record_stock);
            $tStock->create();
        }

        /**
         * Step 3 - Decrease sortir_qty by pd_kg
         */

        foreach($details as $packing_detail) {

            $decrease_kg = (float) $packing_detail['pd_bizhub_kg'];
            $sql = "UPDATE sortir_bizhub_detail SET sortir_bizhub_det_qty = sortir_bizhub_det_qty - ".$decrease_kg."
                        WHERE sortir_bizhub_det_id = ".$packing_detail['sortir_bizhub_det_id'];
            $this->db->query($sql);
        }
    }


    public function removePacking($packing_bizhub_id) {

        $ci = & get_instance();
        /*if ($this->isRefferenced($packing_bizhub_id)){
            //if packing_bizhub_id is used in shipping_detail then delete data shipping also
            $ci->load->model('agripro/shipping_detail');
            $tShippingDetail = $ci->shipping_detail;

            $tShippingDetail->removeByPackingID($packing_bizhub_id);
        }*/

        $ci->load->model('agripro/stock');
        $tStock = $ci->stock;

        $ci->load->model('agripro/packing_bizhub_detail');
        $tPackingDetail = $ci->packing_bizhub_detail;

        /**
         * Steps to Delete Packing
         *
         * 1. Remove all stock_detail first
         * 2. When loop for delete stock_detail, save data sortir in array
         * 3. Delete data packing in stock
         * 4. Loop data sortir for delete data sortir in stock and restore qty to sortir
         * 5. Delete data master packing
         */

        $data_sortir = array();
        $tPackingDetail->setCriteria('pd.packing_bizhub_id = '.$packing_bizhub_id);
        $details = $tPackingDetail->getAll();
        $loop = 0;
        foreach($details as $packing_detail) {
            $data_sortir[$loop]['sortir_bizhub_det_id'] = $packing_detail['sortir_bizhub_det_id'];
            $data_sortir[$loop]['restore_store_qty'] = $packing_detail['pd_bizhub_kg'];
            $loop++;

            $tPackingDetail->remove($packing_detail['pd_bizhub_id']);
        }

        /**
         * Delete data stock by packing_bizhub_id
         */
        $itemPacking = $this->get($packing_bizhub_id);
        $tStock->deleteByReference($packing_bizhub_id, 'PACKING_BIZHUB');

        /**
         * loop for delete data stock by sortir_bizhub_det_id and restore store_qty in table sortir
         */
        foreach($data_sortir as $sortir) {
            //delete data stock by sortir_bizhub_det_id
            $tStock->deleteByReferenceComplete($sortir['sortir_bizhub_det_id'], 'SORTIR_PACKING_BIZHUB', 'OUT', $itemPacking['packing_bizhub_date'], $sortir['restore_store_qty']);

            //restore store qty
            $increase_kg = (float) $sortir['restore_store_qty'];
            $sql = "UPDATE sortir_bizhub_detail SET sortir_bizhub_det_qty = sortir_bizhub_det_qty + ".$increase_kg."
                        WHERE sortir_bizhub_det_id = ".$sortir['sortir_bizhub_det_id'];

            $this->db->query($sql);

        }

        /**
         * Delete data master packing
         */
        $this->remove($packing_bizhub_id);
    }

}
/* End of file Groups.php */