<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Product_controller
* @version 07/05/2015 12:18:00
*/
class Stock_summary_controller {

    function getSummary() {

        $sc_code = getVarClean('sc_code','str','');
        $sc_code = explode(",", $sc_code);
        try {

            $ci = & get_instance();
            $ci->load->model('agripro/stock_summary');
            $tStockSummary = $ci->stock_summary;

            $sql = "select * from product";
            $query = $tStockSummary->db->query($sql);
            $items = $query->result_array();

            $output = '';
            $no = 1;
            foreach($items as $item) {
                $output .= '
                    <tr>
                        <td>'.$no++.'</td>
                        <td>'.$item['product_code'].'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProduct($sc_code, $item['product_id'])).'</td>
                    </tr>
                ';
            }

        }catch (Exception $e) {
            echo $e->getMessage();
        }

        echo $output;
        exit;
    }

    function getFullSummary() {
        try {

            $ci = & get_instance();
            $ci->load->model('agripro/stock_summary');
            $tStockSummary = $ci->stock_summary;

            $sql = "select * from product";
            $query = $tStockSummary->db->query($sql);
            $items = $query->result_array();

            $output = '';
            $no = 1;
            foreach($items as $item) {
                $output .= '
                    <tr>
                        <td>'.$no++.'</td>
                        <td>'.$item['product_code'].'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProductKerinci("'RAW_MATERIAL_STOCK'", $item['product_id'])).'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProductKerinci("'DRYING_STOCK','SORTIR_STOCK','PRODUCTION_STOCK' ", $item['product_id'])).'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProductKerinci("'PACKING_STOCK'", $item['product_id'])).'</td>
                    </tr>
                ';
            }

        }catch (Exception $e) {
            echo $e->getMessage();
        }

        echo $output;
        exit;
    }

    function getFullSummaryBizhub() {
        try {

            $ci = & get_instance();
            $ci->load->model('agripro/stock_summary');
            $tStockSummary = $ci->stock_summary;

            $sql = "select * from product";
            $query = $tStockSummary->db->query($sql);
            $items = $query->result_array();

            $output = '';
            $no = 1;
            foreach($items as $item) {
                $output .= '
                    <tr>
                        <td>'.$no++.'</td>
                        <td>'.$item['product_code'].'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProduct("'RAW_MATERIAL_STOCK_BIZHUB'", $item['product_id'])).'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProduct("'DRYING_STOCK_BIZHUB','SORTIR_STOCK_BIZHUB','PRODUCTION_STOCK_BIZHUB' ", $item['product_id'])).'</td>
                        <td align="right">'.($tStockSummary->getSummaryPerProduct("'PACKING_STOCK_BIZHUB'", $item['product_id'])).'</td>
                    </tr>
                ';
            }

        }catch (Exception $e) {
            echo $e->getMessage();
        }

        echo $output;
        exit;
    }


}

/* End of file Warehouse_controller.php */