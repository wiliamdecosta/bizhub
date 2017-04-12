<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Json library
 * @class sortir_controller
 * @version 07/05/2015 12:18:00
 */
class Sortir_bizhub_production_controller
{

    function readLov()
    {
        permission_check('view-tracking');

        $start = getVarClean('current', 'int', 0);
        $limit = getVarClean('rowCount', 'int', 5);

        $sort = getVarClean('sort', 'str', 'product_name');
        $dir = getVarClean('dir', 'str', 'asc');

        $searchPhrase = getVarClean('searchPhrase', 'str', '');
        $product_id = getVarClean('product_id', 'int', 0);

        $data = array('rows' => array(), 'success' => false, 'message' => '', 'current' => $start, 'rowCount' => $limit, 'total' => 0);

        try {

            $ci = &get_instance();
            $ci->load->model('agripro/sortir_bizhub');
            $table = $ci->sortir_bizhub;

            if(!empty($product_id))
                $table->setCriteria("sr.product_id = ".$product_id);

            if (!empty($searchPhrase)) {
                $table->setCriteria("(sr.sortir_id ilike '%" . $searchPhrase . "%' or pr.product_code ilike '%" . $searchPhrase . "%')");
            }

            $start = ($start - 1) * $limit;
            $items = $table->getAll($start, $limit, $sort, $dir);
            $totalcount = $table->countAll();

            $data['rows'] = $items;
            $data['success'] = true;
            $data['total'] = $totalcount;

        } catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }

    function crud()
    {

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

    function create()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/sortir_bizhub');
        $table = $ci->sortir_bizhub;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)) {
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'CREATE';
        $errors = array();

        if (isset($items[0])) {
            $numItems = count($items);
            for ($i = 0; $i < $numItems; $i++) {
                try {

                    $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items[$i]);
                    $table->create();

                    $table->db->trans_commit(); //Commit Trans

                } catch (Exception $e) {

                    $table->db->trans_rollback(); //Rollback Trans
                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0) {
                $data['message'] = $numErrors . " from " . $numItems . " record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- " . implode("<br/>- ", $errors) . "";
            } else {
                $data['success'] = true;
                $data['message'] = 'Data added successfully';
            }
            $data['rows'] = $items;
        } else {

            try {
                $table->db->trans_begin(); //Begin Trans

                $table->setRecord($items);
                $table->create();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data added successfully';

            } catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function update()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/sortir');
        $table = $ci->sortir;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)) {
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'UPDATE';

        if (isset($items[0])) {
            $errors = array();
            $numItems = count($items);
            for ($i = 0; $i < $numItems; $i++) {
                try {
                    $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items[$i]);
                    $table->update();

                    $table->db->trans_commit(); //Commit Trans

                    $items[$i] = $table->get($items[$i][$table->pkey]);
                } catch (Exception $e) {
                    $table->db->trans_rollback(); //Rollback Trans

                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0) {
                $data['message'] = $numErrors . " from " . $numItems . " record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- " . implode("<br/>- ", $errors) . "";
            } else {
                $data['success'] = true;
                $data['message'] = 'Data update successfully';
            }
            $data['rows'] = $items;
        } else {

            try {
               
                //  check if sortir id already packed 
                $check = $table->is_packing($items[$table->pkey]);
                
                if($check > 0){
                    throw new Exception('Update Failed, This items already packed !');
                };
                
                $table->db->trans_begin(); //Begin Trans

                $table->setRecord($items);
                $table->update();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data update successfully';

                $data['rows'] = $table->get($items[$table->pkey]);
                        
            } catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function destroy()
    {
        $ci = &get_instance();
        $ci->load->model('agripro/sortir');
        $table = $ci->sortir;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try {
            
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    if (empty($value)) throw new Exception('Empty parameter');

                    $table->remove($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            } else {
                $items = (int)$items;
                if (empty($items)) {
                    throw new Exception('Empty parameter');
                };
                
                //  check if sortir id already packed 
                if($table->is_packing($items) > 0){
                    throw new Exception('Delete Failed, Item Already Packed !');
                };
                $table->remove($items);
                $data['rows'][] = array($table->pkey => $items);
                $data['total'] = $total = 1;
            }

            $data['success'] = true;
            $data['message'] = $total . ' Data deleted successfully';

            $table->db->trans_commit(); //Commit Trans
                    
        } catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans
            $data['message'] = $e->getMessage();
            $data['rows'] = array();
            $data['total'] = 0;
        }
        return $data;
    }

    function read()
    {

        $page = getVarClean('page', 'int', 1);
        $limit = getVarClean('rows', 'int', 5);
        $sidx = getVarClean('sidx', 'str', 'sortir_bizhub_id');
        $sord = getVarClean('sord', 'str', 'desc');


        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');


        try {

            $ci = &get_instance();
            $ci->load->model('agripro/sortir_bizhub');
            $table = $ci->sortir_bizhub;

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
			$report = getVarClean('report', 'int', 0);
            if ($report == 1) {
                $req_param['where'][] = 'sr.in_biz_det_id is null and sr.production_bizhub_id is not null 
                                         and ((sr.sortir_bizhub_qty - total_det_qty_init <> 0 and total_det_qty <> 0) 
                                                                       or count_detail = 0)';
            }else{
              
                   $req_param['where'][] = 'sr.in_biz_det_id is null and sr.production_bizhub_id is not null ';
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

        } catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }

    function get_availableqty()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/sortir');
        $table = $ci->sortir;

        $sm_id = getVarClean('sm_id', 'int', 0);

        $qty = explode('|',$table->get_availableqty($sm_id));

        $out['avaqty'] = $qty[0];
        $out['srqty'] = $qty[1];
        $out['qty_bersih'] = $qty[2];
        $out['tgl_prod'] = $qty[3];

        echo json_encode($out);
        exit;
    }

    function get_availableqty_detail_prd()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/sortir_bizhub');
        $table = $ci->sortir_bizhub;

        $sm_id = getVarClean('production_bizhub_id', 'int', 0);
        $sortir_id = getVarClean('sortir_bizhub_id', 'int', 0);

        $qty = explode('|',$table->get_availableqty_detail_prd($sm_id, $sortir_id));

        $out['avaqty'] = $qty[0];
        $out['srqty'] = $qty[1];
        $out['qty_bersih'] = $qty[2];
        $out['tgl_prod'] = $qty[3];

        echo json_encode($out);
        exit;
    }

    function list_product()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/sortir');
        $table = $ci->sortir;
		
		$sm_id = getVarClean('sortir_id', 'int', 0);
        $product_id = getVarClean('product_id', 'int', 0);
        //$sortir_id = getVarClean('sortir_id', 'int', 0);

        $result = $table->list_product_prd_id($sm_id, $product_id);
        echo "<select>";
        foreach ($result as $value) {
            echo "<option value=" . $value['product_id'] . ">" . strtoupper($value['product_code']) . "</option>";
        }
        echo "</select>";
        exit;
    }

	function upd_tgl_prod(){
		
        $ci = & get_instance();
        $ci->load->model('agripro/sortir');
        $table = $ci->sortir;
		
        $sm_id = getVarClean('sm_id','str','');
        $tgl_prod = getVarClean('tgl_prod','str','');
		
        try{
            $table->upd_tgl_prod($sm_id, $tgl_prod);
            $data['success'] = true;
            $data['message'] = 'Succesfully ';
			}catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }
		
        echo json_encode($data);
        exit;
		
		
    }
	
}

/* End of file Warehouse_controller.php */