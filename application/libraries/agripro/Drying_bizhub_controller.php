<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class incoming_goods_detail_controller
* @version 07/05/2015 12:18:00
*/
class Drying_bizhub_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','in_biz_det_id');
        $sord = getVarClean('sord','str','asc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');
        $shipping_id = getVarClean('shipping_id','int',0);
        $is_drying = getVarClean('is_drying','int',0);

        try {

            $ci = & get_instance();
            $ci->load->model('agripro/drying_bizhub');
            $table = $ci->drying_bizhub;

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
			if($is_drying == 1){
                $req_param['where'] = array("(incd.in_biz_drying_date is null or incd.qty_netto_init is null)");
			}else{
                $req_param['where'] = array("incd.qty_netto_init > 0 ");
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



    function crud() {

        $data = array();
        $oper = getVarClean('oper', 'str', '');
        switch ($oper) {
           
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

	function update()
    {

        $ci = &get_instance();
        $ci->load->model('agripro/drying_bizhub');
        $table = $ci->drying_bizhub;

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
                $table->db->trans_begin(); //Begin Trans
                // Check QTY used by production
                $is_null = $table->checkQtyUsedProd($items);
                if($is_null > 0){
                    throw new Exception('Can not edit . This record has been used for Production Proccess');
                }

                ######################################
                ### 1. Insert Stok DRYING_STOCK (IN)
                ### 2. Insert Stock RAW MATERIAL (Out)
                ### 3. Update SM QTY
                ######################################


                $table->setRecord($items);
                //$is_null = $table->checkDryingQTY($table->record);
                $is_exist = $table->IsStockExist($table->record);


                // Check  Drying QTY is null or not
                // IF Null = Insert stock  else Update Stock

                if($is_exist < 1){
                    $table->InsertStock($items);
                }else{
                    $table->UpdateStock($table->record);
                }

                $table->update();

                $table->updateQtyRM($table->record);

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data update successfully'.$is_null;

                $data['rows'] = $table->get($items[$table->pkey]);
            } catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;
		var_dump ($items);
    }


    function destroy() {
        $ci = & get_instance();
        $ci->load->model('agripro/incoming_goods_detail');
        $table = $ci->incoming_goods_detail;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try{
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)){
                foreach ($items as $key => $value){
                    if (empty($value)) throw new Exception('Empty parameter');

                    $table->removeItems($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            }else{
                $items = (int) $items;
                if (empty($items)){
                    throw new Exception('Empty parameter');
                };

                $table->removeItems($items);
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

}

/* End of file incoming_goods_detail_controller.php */