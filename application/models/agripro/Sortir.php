<?php

/**
 * Raw Material Model
 *
 */
class Sortir extends Abstract_model {

    public $table           = "sortir";
    public $pkey            = "sortir_id";
    public $alias           = "sr";

    public $fields          = array(
                                'sortir_id'     => array('pkey' => true, 'type' => 'int', 'nullable' => false, 'unique' => true, 'display' => 'ID Sortir'),
                                'product_id'    => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Product ID'),
                                'sm_id'         => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Stock Material ID'),
                                'production_id' => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Production ID'),
                                'sortir_tgl'      => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Tanggal'),
                                'sortir_qty'      => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'QTY')

                            );

    public $selectClause    = "sr.sortir_id, sr.product_id, sr.sm_id, sr.production_id, sr.sortir_tgl, sr.sortir_qty, fm.fm_code, fm.fm_name,
								sm.sm_no_trans, sm.sm_qty_bersih_init sm_qty_bersih, pr.product_id, pr.product_name, pr.product_code, production.production_code,
								sr.qty_detail, sr.qty_detail_init, sr.total_detail
								";
    public $fromClause      = "v_sortir sr
								left join stock_material sm on sr.sm_id = sm.sm_id
                                left join production on sr.production_id = production.production_id
								left join product pr on sr.product_id = pr.product_id
								left join farmer fm on sm.fm_id = fm.fm_id
								";

    public $refs            = array("sortir_detail"=>"sortir_id");

    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
           // $this->record['sortir_tgl'] = date('Y-m-d');

        }else {
            //do something
            //example:
           // $this->record['sortir_tgl'] = date('Y-m-d');
        }
        return true;
    }

	function get_availableqty($sm_id){

		$sql = "SELECT (select sm_qty_bersih_init from stock_material where sm_id = $sm_id ) -
						COALESCE (sum(sortir_qty),0) as avaqty, COALESCE (sum(sortir_qty),0) as srtqty ,
						COALESCE((select sm_qty_bersih_init from stock_material where sm_id = $sm_id ),0) qty_bersih,
						(select sm_tgl_produksi from stock_material where sm_id = $sm_id ) tgl_prod
						from sortir where sm_id = $sm_id ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

	}

    function get_availableqty_detail($sm_id, $sortir_id){

		$sql = " SELECT (select sm_qty_bersih_init from stock_material where sm_id = $sm_id ) -
						COALESCE (sum(sortir_detail_qty_init),0) as avaqty,
                        COALESCE (sum(sortir_detail_qty_init),0) as srtqty ,
						COALESCE((select sm_qty_bersih_init from stock_material where sm_id = $sm_id ),0) qty_bersih,
						(select sm_tgl_produksi from stock_material where sm_id = $sm_id ) tgl_prod
						from sortir_detail
                        where sortir_id = $sortir_id ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

	}

    function get_availableqty_detail_prd($sm_id, $sortir_id){

		$sql = " SELECT (select production_qty_init from production where production_id = $sm_id ) -
						COALESCE (sum(sortir_detail_qty_init),0) as avaqty,
                        COALESCE (sum(sortir_detail_qty_init),0) as srtqty ,
						COALESCE((select production_qty_init from production where production_id = $sm_id ),0) qty_bersih,
						(select production_date from production where production_id = $sm_id ) tgl_prod
						from sortir_detail
                        where sortir_id = $sortir_id ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return floatval($row['avaqty']) .'|'. floatval($row['srtqty']).'|'. floatval($row['qty_bersih']).'|'. $row['tgl_prod'];

	}

	function list_product($sm_id, $sortir_id){

        $sql = "
				SELECT *
				FROM (
						SELECT *
							FROM product
								WHERE parent_id = (	select  coalesce(parent_id,product_id)
														from product
															where product_id = (select product_id
																					from stock_material
																					where sm_id = $sm_id)
															)
						UNION ALL
						SELECT *
							FROM product
								WHERE product_code IN ('LOST')
						) as a
					WHERE a.product_id not in (select distinct product_id
														from sortir_detail
															where sortir_id = $sortir_id
                                                            union all
                                                            select distinct product_id
														from sortir
															where sortir_id = $sortir_id
                                                            )
				";
        $q = $this->db->query($sql);
        return $q->result_array();

    }

	function list_product_prd_id($sm_id,$product_id){
		$type = $this->get_type_prod($sm_id);
        // 0 asalan
		if($type == '0') {
			$sql = "
				SELECT *
				FROM (
						SELECT *
							FROM product
								WHERE  upper(product_code) NOT LIKE '%REJECT%'
								AND (product_category_id = 2 or product_category_id is null)
								AND parent_id is not NULL
						UNION ALL
						SELECT *
							FROM product
								WHERE (parent_id = $product_id  )
								AND upper(product_code) LIKE '%REJECT%'
						UNION ALL
						SELECT *
							FROM product
								WHERE product_code IN ('LOST','STICK')
						) as a
					WHERE a.product_id not in (select distinct product_id
														from sortir_detail
															where sortir_id = $sm_id )
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
								AND product_category_id = 2
						UNION ALL
						SELECT *
							FROM product
								WHERE (parent_id = $product_id or parent_id  = (select case when sm_id is null then 1 else 0 end
																					from sortir where sortir_id = $sm_id limit 1) )
								AND upper(product_code) LIKE '%REJECT%'
						UNION ALL
						SELECT *
							FROM product
								WHERE product_code IN ('LOST')
						) as a
					WHERE a.product_id not in (select distinct product_id
														from sortir_detail
															where sortir_id = $sm_id )
				";
		}

        $q = $this->db->query($sql);
        return $q->result_array();

    }


    function list_product_prd($sm_id, $sortir_id){

        $sql = "
				SELECT *
				FROM (
						SELECT *
							FROM product
								WHERE parent_id = (	select  coalesce(parent_id,product_id)
														from product
															where product_id = (select product_id
																					from production
																					where production_id = $sm_id)
															)
						UNION ALL
						SELECT *
							FROM product
								WHERE product_code IN ('LOST')
						) as a
					WHERE a.product_id not in (select distinct product_id
														from sortir_detail
															where sortir_id = $sortir_id
                                                            union all
                                                            select distinct product_id
														from sortir
															where sortir_id = $sortir_id
                                                            )
				";
        $q = $this->db->query($sql);
        return $q->result_array();

    }

	function upd_tgl_prod($sm_id,$tgl_prod){

        $sql = " UPDATE stock_material
					set sm_tgl_produksi = to_date('".$tgl_prod."','yyyy-mm-dd')
					where sm_id = $sm_id
		";
        $q = $this->db->query($sql);

    }

	function get_type_prod($sm_id){

		$sql = " SELECT case when b.product_category_id = 1 then 1 else 0 end total
                        from sortir a, product b
                            where a.sortir_id = $sm_id
                            and a.product_id = b.product_id limit 1  ";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return $row['total'];

	}

    function is_packing($sm_id){

        /* $sql = " SELECT COUNT(*) total
                        from packing_detail
                            where sortir_detail_id = $sm_id";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $query->free_result();

        return $row['total']; */
		return 0;
    }

}

/* End of file Groups.php */