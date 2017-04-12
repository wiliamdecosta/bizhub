<?php

/**
 * Product Model
 *
 */
class Stock_summary extends Abstract_model {

    public $table           = "";
    public $pkey            = "";
    public $alias           = "";

    public $fields          = array();

    public $selectClause    = "";
    public $fromClause      = "";


    function __construct() {
        parent::__construct();
    }

    function validate() {
        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
           /* $this->record['created_date'] = date('Y-m-d');
            $this->record['created_by'] = $userdata->username;
            $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/

        }else {
            //do something
            //example:
           /* $this->record['updated_date'] = date('Y-m-d');
            $this->record['updated_by'] = $userdata->username;*/
            //if false please throw new Exception
        }
        return true;
    }

    function getSummary($sc_code = '') {

        if(empty($sc_code)) return array();

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        $sql = "select wh_id, wh_code, product_id, product_code, sum(total) as total_stock
                from
                (
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code = ? and a.wh_id = ?
                    and a.stock_tgl_masuk is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                    union
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(0 - a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code = ? and a.wh_id = ?
                    and a.stock_tgl_keluar is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                ) as stockis
                group by wh_id, wh_code, product_id, product_code";

        $query = $this->db->query($sql, array($sc_code, $userdata['wh_id'], $sc_code, $userdata['wh_id']));
        return $query->result_array();
    }


    function getSummaryPerProduct($sc_code, $product_id = '') {

        if(empty($sc_code)) return array();

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if(gettype($sc_code) == 'array') {
            $sc_code = join(',', $sc_code);
        }

        $sql = "select wh_id, wh_code, product_id, product_code, sum(total) as total_stock
                from
                (
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code in ($sc_code) and a.wh_id = ?
                    and a.product_id = ?
                    and a.stock_tgl_masuk is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                    union
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(0 - a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code in ($sc_code) and a.wh_id = ?
                    and a.product_id = ?
                    and a.stock_tgl_keluar is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                ) as stockis
                group by wh_id, wh_code, product_id, product_code";

        $query = $this->db->query($sql, array($userdata['wh_id'], $product_id, $userdata['wh_id'], $product_id));
        $item = $query->row_array();

        return (int)$item['total_stock'];
    }

    function getSummaryPerProductKerinci($sc_code, $product_id = '') {

        if(empty($sc_code)) return array();

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if(gettype($sc_code) == 'array') {
            $sc_code = join(',', $sc_code);
        }

        $sql = "select wh_id, wh_code, product_id, product_code, sum(total) as total_stock
                from
                (
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code in ($sc_code) and a.wh_id = ?
                    and a.product_id = ?
                    and a.stock_tgl_masuk is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                    union
                    select a.wh_id, c.wh_code, a.product_id, d.product_code, sum(0 - a.stock_kg) as total
                    from stock a
                    left join stock_category b on a.sc_id = b.sc_id
                    left join warehouse c on a.wh_id = c.wh_id
                    left join product d on a.product_id = d.product_id
                    where b.sc_code in ($sc_code) and a.wh_id = ?
                    and a.product_id = ?
                    and a.stock_tgl_keluar is not null
                    group by a.wh_id, c.wh_code, a.product_id, d.product_code
                ) as stockis
                group by wh_id, wh_code, product_id, product_code";

        $query = $this->db->query($sql, array(1, $product_id, 1, $product_id));
        $item = $query->row_array();

        return (int)$item['total_stock'];
    }
}

/* End of file Groups.php */