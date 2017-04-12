<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Packing_bizhub_controller
* @version 17/06/2016 05:29:00
*/
class Packing_bizhub_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','packing_bizhub_id');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/packing_bizhub');
            $table = $ci->packing_bizhub;

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
            $req_param['where'] = array('pack.packing_bizhub_id NOT IN (select packing_bizhub_id from shipping_bizhub_detail)');

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


    function readHistory() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','packing_bizhub_id');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/packing_bizhub');
            $table = $ci->packing_bizhub;

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
            $req_param['where'] = array();

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

        $sort = getVarClean('sort','str','packing_bizhub_id');
        $dir  = getVarClean('dir','str','asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');
        $for_shipping = getVarClean('for_shipping','str','Y');

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/packing_bizhub');
            $table = $ci->packing_bizhub;

            $userdata = $ci->ion_auth->user()->row();
             $table->setCriteria('pack.packing_bizhub_id NOT IN (select packing_bizhub_id from shipping_bizhub_detail)');

            $table->setCriteria('pack.warehouse_id = 999');

            if(!empty($searchPhrase)) {
                $table->setCriteria("pack.packing_bizhub_batch_number ilike '%".$searchPhrase."%' or prod.product_code ilike '%".$searchPhrase."%'");
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
        $ci->load->model('agripro/packing_bizhub');
        $table = $ci->packing_bizhub;

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
        $ci->load->model('agripro/packing_bizhub');
        $table = $ci->packing_bizhub;

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
        $ci->load->model('agripro/packing_bizhub');
        $table = $ci->packing_bizhub;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try{
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)){
                foreach ($items as $key => $value){
                    if (empty($value)) throw new Exception('Empty parameter');

                    $table->removePacking($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            }else{
                $items = (int) $items;
                if (empty($items)){
                    throw new Exception('Empty parameter');
                };

                $table->removePacking($items);
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


    function createForm() {

        $ci = & get_instance();
        $ci->load->model('agripro/packing_bizhub');
        $table = $ci->packing_bizhub;

        $data = array('success' => false, 'message' => '');
        $table->actionType = 'CREATE';

        /**
         * Data master
         */
        $packing_bizhub_kg = getVarClean('packing_bizhub_kg','float',0);
        $packing_bizhub_date = getVarClean('packing_bizhub_date','str','');
        $product_id = getVarClean('product_id','int',0);
        $userdata = $ci->ion_auth->user()->row();

        /**
         * Data details
         */
        $sortir_bizhub_detail_ids = (array)$ci->input->post('sortir_bizhub_det_id');
        $weights = (array)$ci->input->post('weight');
        $product_ids = (array)$ci->input->post('product_ids');

        try{

            if(count($sortir_bizhub_detail_ids) == 0) {
                throw new Exception('Data source material must be filled');
            }

            $table->db->trans_begin(); //Begin Trans

                $items = array(
                    'packing_bizhub_kg' => $packing_bizhub_kg,
                    'packing_bizhub_date' => $packing_bizhub_date,
                    'product_id' => $product_id,
                    'warehouse_id' => 999, //get from session
                    'packing_bizhub_batch_number' => '',
                    'packing_bizhub_serial' => '',
                );

                $table->setRecord($items);

                $batch_number = $table->getBatchNumber();
                $table->record['packing_bizhub_batch_number'] = $batch_number['batch_number'];
                $table->record['packing_bizhub_serial'] = $batch_number['serial_number'];
                $table->record[$table->pkey] = $table->generate_id($table->table,$table->pkey);


                $record_detail = array();
                $ci->load->model('agripro/packing_bizhub_detail');
                $tableDetail = $ci->packing_bizhub_detail;
                $tableDetail->actionType = 'CREATE';

                $total_source_kg = 0;

                for($i = 0; $i < count($sortir_bizhub_detail_ids); $i++) {
                    $record_detail[] = array(
                        'packing_bizhub_id' => $table->record[$table->pkey],
                        'sortir_bizhub_det_id' => $sortir_bizhub_detail_ids[$i],
                        'pd_bizhub_kg' => $weights[$i]
                    );

                    //jika product_id master !== product_id detail maka throw exception
                    if($product_id != $product_ids[$i]) {
                        throw new Exception('Item of source package has different product');
                    }

                    $total_source_kg += $weights[$i];
                    //cek data
                    //if ok
                    //tampung dulu ke suatu array
                }

                //cek data apakah total_source_kg == packing_kg
                if($packing_bizhub_kg != $total_source_kg) {
                    throw new Exception('Total weight of sources ('.$total_source_kg.' Kg) does not match with packing weight ('.$packing_bizhub_kg.' Kg)');
                }


                $table->create();
                foreach($record_detail as $item_detail) {
                    $tableDetail->setRecord($item_detail);
                    $tableDetail->create();
                }

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


    /*function updateForm() {

        $ci = & get_instance();
        $ci->load->model('agripro/packing');
        $table = $ci->packing;

        $data = array('success' => false, 'message' => '');
        $table->actionType = 'UPDATE';

        $packing_id = getVarClean('packing_id','int',0);
        $packing_kg = getVarClean('packing_kg','float',0);
        $packing_tgl = getVarClean('packing_tgl','str','');
        $product_id = getVarClean('product_id','int',0);
        $userdata = $ci->ion_auth->user()->row();


        $pd_id = (array) $ci->input->post('pd_id');
        $sortir_detail_ids = (array) $ci->input->post('sortir_id');
        $weights = (array) $ci->input->post('weight');

        try{

            if(count($sortir_ids) == 0) {
                throw new Exception('Data source material must be filled');
            }

            $table->db->trans_begin(); //Begin Trans

                $items = array(
                    'packing_id' => $packing_id,
                    'packing_kg' => $packing_kg,
                    'packing_tgl' => $packing_tgl,
                    'product_id' => $product_id
                );

                $table->setRecord($items);

                $record_detail = array();
                $ci->load->model('agripro/packing_detail');
                $tableDetail = $ci->packing_detail;

                for($i = 0; $i < count($sortir_ids); $i++) {

                    $record_detail[] = array (
                        'pd_id' => $pd_id[$i],
                        'packing_id' => $packing_id,
                        'sortir_id' => $sortir_ids[$i],
                        'pd_kg' => $weights[$i]
                    );

                    //cek data
                    //if ok
                    //tampung dulu ke suatu array
                }


                foreach($record_detail as $item_detail) {

                    if(empty($item_detail['pd_id'])) {
                        $tableDetail->actionType = 'CREATE';
                        unset($item_detail['pd_id']);
                        $tableDetail->setRecord($item_detail);
                        $tableDetail->create();
                    }else {
                        //do nothing
                    }
                }

                $table->update();

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
*/


}

/* End of file Warehouse_controller.php */