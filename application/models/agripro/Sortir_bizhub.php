<?php

/**
 * Sortir Bizhub Model
 *
 */
class Sortir_bizhub extends Abstract_model {

    public $table           = "sortir_bizhub";
    public $pkey            = "sortir_bizhub_id";
    public $alias           = "sr";

    public $fields          = array(
                                'sortir_bizhub_id'  => array('pkey' => true, 'type' => 'int', 'nullable' => false, 'unique' => true, 'display' => 'ID Sortir'),
                                'product_id'        => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'in_biz_det_id'     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Bizhub Detail ID'),
                                'production_bizhub_id' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Production ID'),

                                'sortir_bizhub_tgl'      => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Tanggal'),
                                'sortir_bizhub_qty'      => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'QTY')
                            );

    public $selectClause    = "sr.*,
								sm.packing_batch_number, sm.qty_source, sm.qty_rescale, sm.qty_bruto, sm.qty_netto, pr.product_name, pr.product_code, production_bizhub.production_bizhub_code";
    public $fromClause      = "(select sr.*,  (select count(*) from sortir_bizhub_detail std
                                  where std.sortir_bizhub_id = sr.sortir_bizhub_id ) count_detail,
                                  (select sum(sortir_bizhub_det_qty_init) from sortir_bizhub_detail std
                                  where std.sortir_bizhub_id = sr.sortir_bizhub_id) total_det_qty_init,
                                  (select sum(sortir_bizhub_det_qty) from sortir_bizhub_detail std
                                  where std.sortir_bizhub_id = sr.sortir_bizhub_id) total_det_qty from sortir_bizhub sr ) sr
								left join incoming_bizhub_detail sm on sr.in_biz_det_id = sm.in_biz_det_id
                                left join production_bizhub on sr.production_bizhub_id = production_bizhub.production_bizhub_id
								left join product pr on sr.product_id = pr.product_id
								";

    public $refs            = array("sortir_bizhub_detail"=>"sortir_bizhub_id");

    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
            $this->record['sortir_bizhub_tgl'] = date('Y-m-d');

        }else {
            //do something
            //example:
            $this->record['sortir_bizhub_tgl'] = date('Y-m-d');
            $this->record['qty_netto_init'] = $userdata['user_name'];
            //if false please throw new Exception
        }
        return true;
    }

    function is_packing($sm_id){

        $sql = " SELECT COUNT(*) total
                        from packing_bizhub_detail
                            where sortir_bizhub_det_id = $sm_id";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return $row['total'];
    }

    function get_availableqty_detail($in_biz_det_id, $sortir_bizhub_id){

        $sql = " SELECT (select qty_netto_init from incoming_bizhub_detail where in_biz_det_id = $in_biz_det_id ) -
                  COALESCE (sum(sortir_bizhub_det_qty_init),0) as avaqty,
                  COALESCE (sum(sortir_bizhub_det_qty_init),0) as srtqty,
                  COALESCE((select qty_netto_init from incoming_bizhub_detail where in_biz_det_id = $in_biz_det_id ),0) qty_bersih,
                  (select in_biz_drying_date from incoming_bizhub_detail where in_biz_det_id = $in_biz_det_id ) tgl_prod
                  from sortir_bizhub_detail
                  where sortir_bizhub_id = $sortir_bizhub_id  ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

    }

    function get_availableqty_detail_prd($sm_id, $sortir_bizhub_id){

        $sql = " SELECT (select production_bizhub_qty_init from production_bizhub where production_bizhub_id = $sm_id ) -
                        COALESCE (sum(sortir_bizhub_det_qty_init),0) as avaqty,
                        COALESCE (sum(sortir_bizhub_det_qty_init),0) as srtqty ,
                        COALESCE((select production_bizhub_qty_init from production_bizhub where production_bizhub_id = $sm_id ),0) qty_bersih,
                        (select production_bizhub_date from production_bizhub where production_bizhub_id = $sm_id ) tgl_prod
                        from sortir_bizhub_detail
                        where sortir_bizhub_id = $sortir_bizhub_id ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

    }

    function list_product_prd_id($sm_id,$product_id){
        $type = $this->get_type_prod($sm_id);
        if($type == '0') {
            $sql = "
                SELECT *
                FROM (
                        SELECT *
                            FROM product
                                WHERE product_id = $product_id
                                AND upper(product_code) NOT LIKE '%REJECT%'
                                AND (product_category_id = 2 or product_category_id is null )
                        UNION ALL
                        SELECT *
                            FROM product
                                WHERE (parent_id = $product_id  )
                                AND upper(product_code) LIKE '%REJECT%'
                        UNION ALL
                        SELECT *
                            FROM product
                                WHERE product_code IN ('LOST')
                        ) as a
                    WHERE a.product_id not in (select distinct product_id
                                                        from sortir_bizhub_detail
                                                            where sortir_bizhub_id = $sm_id )
                ";
        }else{
            $sql = "
                SELECT *
                FROM (
                        SELECT *
                            FROM product
                                WHERE parent_id = $product_id
                                AND upper(product_code) NOT LIKE '%REJECT%'
                        UNION ALL
                        SELECT *
                            FROM product
                                WHERE product_id = $product_id
                                AND upper(product_code) NOT LIKE '%REJECT%'
                                AND product_category_id = 1
                        UNION ALL
                        SELECT *
                            FROM product
                                WHERE (parent_id = $product_id or parent_id  = (select case when in_biz_det_id is null then 1 else 0 end
                                                                                    from sortir_bizhub where sortir_bizhub_id = $sm_id limit 1) )
                                AND upper(product_code) LIKE '%REJECT%'
                        UNION ALL
                        SELECT *
                            FROM product
                                WHERE product_code IN ('LOST')
                        ) as a
                    WHERE a.product_id not in (select distinct product_id
                                                        from sortir_bizhub_detail
                                                            where sortir_bizhub_det_id = $sm_id )
                ";
        }

        $q = $this->db->query($sql);
        return $q->result_array();

    }

    function get_type_prod($sm_id){

        $sql = " SELECT case when b.product_category_id = 1 then 1 else 0 end total
                        from sortir_bizhub a, product b
                            where a.sortir_bizhub_id = $sm_id
                            and a.product_id = b.product_id limit 1  ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return $row['total'];

    }

}

/* End of file Groups.php */