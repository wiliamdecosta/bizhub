<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Permission_role_controller
* @version 07/05/2015 12:18:00
*/
class Permission_role_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','permissions.permission_id');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

		$role_id = getVarClean('role_id','int',0);
        try {

            $ci = & get_instance();
            $ci->load->model('administration/permission_role');

            $table = new Permission_role($role_id);


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
            logging('view role permission');
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
                permission_check('can-add-permission');
                $data = $this->create();
            break;

            default :
                permission_check('can-view-permission');
                $data = $this->read();
            break;
        }

        return $data;
    }


    function create() {

        $ci = & get_instance();
        $ci->load->model('administration/permission_role');
        $table = $ci->permission_role;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $items = getVarClean('items','str','');
        $role_id = getVarClean('role_id','int',0);

        $errors = array();

        try{

            $arrItems = json_decode($items);
            $total_execute = 0;

            if(count($arrItems) > 0) {
                $table->db->trans_begin(); //Begin Trans

                foreach($arrItems as $item) {
                    $code = explode(".", $item->id);
                    $allowed_permission = $item->val;

                    if($code[0] == '0' && $allowed_permission == 'Yes') { //data not exist in table
                        //insert data to table
                        $table->actionType = 'CREATE';
                        $table->setRecord(
                            array('role_id' => $role_id,
                                  'permission_id' => $code[1])
                        );
                        $table->create();
                        $total_execute++;

                    }elseif( $code[0] != '0' && $allowed_permission == 'No') {
                        //delete data from table
                        $table->removeItem($role_id, $code[1]);
                        $total_execute++;
                    }
                }

                $table->db->trans_commit(); //Commit Trans
            }

            $data['success'] = true;
            $data['message'] = $total_execute.' data permission has been updated';
            logging('set role permission');
        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans

            $data['message'] = $e->getMessage();
            $data['rows'] = $items;
        }

        return $data;

    }


}

/* End of file Permission_role_controller.php */