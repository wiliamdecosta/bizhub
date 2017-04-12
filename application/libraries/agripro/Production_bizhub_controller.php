<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Product_controller
* @version 07/05/2015 12:18:00
*/
class Production_bizhub_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/production_bizhub');
            $table = $ci->production_bizhub;

            $req_param = array(
                "sort_by" => $sidx,
                "sord" => $sord,
                "limit" => null,
                "field" => null,
                "where" => null,
                "where_in" => null,
                "where_not_in" => null,
                "search" => $_REQUEST['_search'],
                "search_field" => isset($_REQUEST['searchField']) ? $_REQUEST['searchField'] : null,
                "search_operator" => isset($_REQUEST['searchOper']) ? $_REQUEST['searchOper'] : null,
                "search_str" => isset($_REQUEST['searchString']) ? $_REQUEST['searchString'] : null
            );

            // Filter Table
            $p_cat = getVarClean('p_cat','str','');

            if($p_cat === 'stick'){
                $req_param['where'] = array("b.product_category_id = 1 and a.production_bizhub_qty > 0");
            }elseif ($p_cat === 'asalan'){
                $req_param['where'] = array("b.product_category_id = 2 and a.production_bizhub_qty > 0");
            }else{
                $req_param['where'] = array("a.production_bizhub_qty_init > 0 and (production_bizhub_qty = 0)");
            }


            $table->setJQGridParam($req_param);
            $count = $table->countAll();

            if ($count > 0) $total_pages = ceil($count / $limit);
            else $total_pages = 1;

            if ($page > $total_pages) $page = $total_pages;
            $start = $limit * $page - ($limit); // do not put $limit*($page - 1)

            $req_param['limit'] = array(
                'start' => $start,
                'end' => $limit
            );

            $table->setJQGridParam($req_param);

            if ($page == 0) $data['page'] = 1;
            else $data['page'] = $page;

            $data['total'] = $total_pages;
            $data['records'] = $count;

            $data['rows'] = $table->getAll();
            $data['success'] = true;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }
    
    function readLov() {
        permission_check('view-tracking');

        $start = getVarClean('current','int',0);
        $limit = getVarClean('rowCount','int',5);

        $sort = getVarClean('sort','str','category_id');
        $dir  = getVarClean('dir','str','asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/category');
            $table = $ci->category;

            if(!empty($searchPhrase)) {
                $table->setCriteria("(category_id ilike '%".$searchPhrase."%' or category_name ilike '%".$searchPhrase."%')");
            }

            $start = ($start-1) * $limit;
            $items = $table->getAll($start, $limit, $sort, $dir);
            $totalcount = $table->countAll();

            $data['rows'] = $items;
            $data['success'] = true;
            $data['total'] = $totalcount;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }
    
     function readLov_sortir() {
        permission_check('view-tracking');

        $start = getVarClean('current','int',0);
        $limit = getVarClean('rowCount','int',5);

        $sort = getVarClean('sort','str','production_bizhub_id');
        $dir  = getVarClean('dir','str','asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/production_bizhub');
            $table = $ci->production_bizhub;
            
            $table->setCriteria(" production_bizhub_qty is not null 
                                    AND production_bizhub_id not in (select distinct production_bizhub_id 
                                                                        from sortir_bizhub
                                                                            where production_bizhub_id is not null) ");
            if(!empty($searchPhrase)) {
                $table->setCriteria(" (production_bizhub_id ilike '%".$searchPhrase."%' or production_bizhub_code ilike '%".$searchPhrase."%')");
            }

            $start = ($start-1) * $limit;
            $items = $table->getAll($start, $limit, $sort, $dir);
            $totalcount = $table->countAll();

            $data['rows'] = $items;
            $data['success'] = true;
            $data['total'] = $totalcount;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }
    function crud() {

        $data = array();
        $oper = getVarClean('oper', 'str', '');
        switch ($oper) {
            case 'add' :
                permission_check('add-tracking');
                $data = $this->create();
            break;

            case 'edit' :
                permission_check('edit-tracking');
                $data = $this->update();
            break;

            case 'del' :
                permission_check('delete-tracking');
                $data = $this->destroy();
            break;

            default :
                permission_check('view-tracking');
                $data = $this->read();
            break;
        }

        return $data;
    }


    function create() {

        $ci = & get_instance();
        $ci->load->model('agripro/production_bizhub');
        $table = $ci->production_bizhub;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'CREATE';
        $errors = array();

        if (isset($items[0])){
            $numItems = count($items);
            for($i=0; $i < $numItems; $i++){
                try{

                    $table->db->trans_begin(); //Begin Trans

                        $table->setRecord($items[$i]);
                        $table->create();

                    $table->db->trans_commit(); //Commit Trans

                }catch(Exception $e){

                    $table->db->trans_rollback(); //Rollback Trans
                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0){
                $data['message'] = $numErrors." from ".$numItems." record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
            }else{
                $data['success'] = true;
                $data['message'] = 'Data added successfully';
            }
            $data['rows'] =$items;
        }else {

            try{
                $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items);
                    $table->create();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data added successfully';

            }catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function update() {

        $ci = & get_instance();
        $ci->load->model('agripro/production_bizhub');
        $table = $ci->production_bizhub;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'UPDATE';

        if (isset($items[0])){
            $errors = array();
            $numItems = count($items);
            for($i=0; $i < $numItems; $i++){
                try{
                    $table->db->trans_begin(); //Begin Trans

                        $table->setRecord($items[$i]);
                        $table->update();

                    $table->db->trans_commit(); //Commit Trans

                    $items[$i] = $table->get($items[$i][$table->pkey]);
                }catch(Exception $e){
                    $table->db->trans_rollback(); //Rollback Trans

                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0){
                $data['message'] = $numErrors." from ".$numItems." record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
            }else{
                $data['success'] = true;
                $data['message'] = 'Data update successfully';
            }
            $data['rows'] =$items;
        }else {

            try{
                $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items);
                    $table->update();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data update successfully';

                $data['rows'] = $table->get($items[$table->pkey]);
            }catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function destroy() {
        $ci = & get_instance();
        $ci->load->model('agripro/production_bizhub');
        $table = $ci->production_bizhub;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try{
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)){
                foreach ($items as $key => $value){
                    if (empty($value)) throw new Exception('Empty parameter');

                   // $table->remove($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            }else{
                $items = (int) $items;
                if (empty($items)){
                    throw new Exception('Empty parameter');
                };

                $is_null = $table->checkQtyUsedBySortir($items);
                if($is_null > 0){
                    throw new Exception('Can not delete this record (has been used for selection process) ! ');
                }
                $table->removeProduction($items);
                $data['rows'][] = array($table->pkey => $items);
                $data['total'] = $total = 1;
            }

            $data['success'] = true;
            $data['message'] = $total.' Data deleted successfully';

            $table->db->trans_commit(); //Commit Trans

        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans
            $data['message'] = $e->getMessage();
            $data['rows'] = array();
            $data['total'] = 0;
        }
        return $data;
    }

    function readLovProductProduction() {
        permission_check('view-tracking');

        $start = getVarClean('current','int',0);
        $limit = getVarClean('rowCount','int',5);

        $sort = getVarClean('sort','str','product_id');
        $dir  = getVarClean('dir','str','asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/product');
            $table = $ci->product;

            // Filter Table
            $p_cat = getVarClean('p_cat','str','');

            if(strtoupper($p_cat) === 'STICK'){
                $table->setCriteria("prod.parent_id = 1 and upper(prod.product_code) NOT LIKE 'REJECT%' ");
            }elseif (strtoupper($p_cat) === 'ASALAN'){
                $table->setCriteria("prod.product_code in ('KABC','KBBC') ");
            }

            if(!empty($searchPhrase)) {
                $table->setCriteria("(product_code ilike '%".$searchPhrase."%' or product_name ilike '%".$searchPhrase."%')");
            }

            $start = ($start-1) * $limit;
            $items = $table->getAll($start, $limit, $sort, $dir);
            $totalcount = $table->countAll();

            $data['rows'] = $items;
            $data['success'] = true;
            $data['total'] = $totalcount;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }
    function readLovINC() {
        permission_check('view-tracking');

        $start = getVarClean('current','int',0);
        $limit = getVarClean('rowCount','int',5);

        $sort = getVarClean('sort','str','product_id');
        $dir  = getVarClean('dir','str','asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/incoming_goods_detail');
            $table = $ci->incoming_goods_detail;

            $parent_id = getVarClean('parent_id','int',0);
            $product_id = getVarClean('product_id','int',0);
            /*if parent_id = 1 => Set product code = STICK*/
            if($parent_id == 1){
                //$table->setCriteria("prd.product_code = 'STICK' "); // Kusus Stick
                $table->setCriteria("prd.product_category_id = 1 "); // Kusus Stick
            }else{
                $table->setCriteria("prd.parent_id = $product_id "); // Asalan Raw Mat
            }
            $table->setCriteria("qty_netto != 0 "); // Quantity yg bukan 0

            if(!empty($searchPhrase)) {
                $table->setCriteria("(packing_batch_number ilike '%".$searchPhrase."%' or packing_batch_number ilike '%".$searchPhrase."%')");
            }

            $start = ($start-1) * $limit;
            $items = $table->getAll($start, $limit, $sort, $dir);
            $totalcount = $table->countAll();

            $data['rows'] = $items;
            $data['success'] = true;
            $data['total'] = $totalcount;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }

    function createForm2() {
        $ci = & get_instance();
        $ci->load->model('agripro/production_bizhub');
        $table = $ci->production_bizhub;

        $data = array('success' => false, 'message' => '');
        $table->actionType = 'CREATE';

        /**
         * Data master
         */
        $product_id = getVarClean('product_id','int',0);
        $product_date = getVarClean('tgl','str',0);
        $userdata = $ci->ion_auth->user()->row();

        /**
         * Data details
         */
        $array_sm_id = (array)$ci->input->post('row_sm_id');
        $array_weight= (array)$ci->input->post('weight');

        $product_qty = array_sum($array_weight);


        try{

            // Step
            // 1. Generate Production Code
            // 2. Insert Header
            // 3. Insert Detail
            // 4. Update SM (OUT)

            if(count($array_sm_id) == 0) {
                throw new Exception('Data source material must be filled');
            }

            $table->db->trans_begin(); //Begin Trans
            $items = array(
                'product_id' => $product_id,
                'warehouse_id' => $userdata->wh_id,
                'production_bizhub_qty' => $product_qty,
                'production_bizhub_qty_init' => $product_qty,
                'production_bizhub_code' => ' ',
                'production_bizhub_date' => $product_date
            );

            $table->setRecord($items);

            ####################################
            ### Step 1. Generate Production Code
            ####################################

            $table->record['production_bizhub_code'] = $table->genProductionCode();
            $table->record[$table->pkey] = $table->generate_id($table->table,$table->pkey);

            $record_detail = array();
            $ci->load->model('agripro/production_detail_bizhub');
            $tableDetail = $ci->production_detail_bizhub;
            $tableDetail->actionType = 'CREATE';

            for($i = 0; $i < count($array_sm_id); $i++) {
                $record_detail[] = array(
                    'production_bizhub_id' => $table->record[$table->pkey],
                    'in_biz_det_id' => $array_sm_id[$i],
                    'production_bizhub_det_qty' => $array_weight[$i]
                );

            }

            ####################################
            ### Step 2. Insert Master
            ####################################

            $table->create();
            $table->InsertStockMaster($table->record);

            ####################################
            ### Step 3. Insert Detail
            ####################################

            foreach($record_detail as $item_detail) {
                $tableDetail->setRecord($item_detail);
                $tableDetail->create();
            }

            ####################################
            ### Step 4. Insert Stock SM (OUT)
            ####################################

            $table->insertStock($table->record);

            $table->db->trans_commit(); //Commit Trans

            $data['success'] = true;
            $data['message'] = 'Data added successfully';

        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans

            $data['message'] = $e->getMessage();
        }


        echo json_encode($data);
        exit;

    }

}

/* End of file Warehouse_controller.php */