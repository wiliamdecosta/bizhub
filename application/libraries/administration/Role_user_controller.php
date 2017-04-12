<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Role_user_controller
* @version 07/05/2015 12:18:00
*/
class Role_user_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','ru.role_id');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $user_id = getVarClean('user_id','int',0);
        try {

            $ci = & get_instance();
            $ci->load->model('administration/role_user');
            $table = $ci->role_user;

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
            $req_param['where'] = array("ru.user_id = ".$user_id);

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
            logging('view role user');

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
                permission_check('can-add-role');
                $data = $this->create();
            break;

            case 'edit' :
                permission_check('can-edit-role');
                $data = $this->update();
            break;

            case 'del' :
                permission_check('can-delete-role');
                $data = $this->destroy();
            break;

            default :
                permission_check('can-view-role');
                $data = $this->read();
            break;
        }

        return $data;
    }


    function create() {

        $ci = & get_instance();
        $ci->load->model('administration/role_user');
        $table = $ci->role_user;

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
                logging('create role user');

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
        $ci->load->model('administration/role_user');
        $table = $ci->role_user;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'UPDATE';

        try{
            $table->db->trans_begin(); //Begin Trans

                $code = explode('.', $items['id']);
                $user_id = $code[0];
                $role_id = $code[1];

                $sql = "update role_user set role_id = ?
                            where user_id = ? and role_id = ?";

                $table->db->query($sql, array($items['role_id'], $user_id, $role_id));

            $table->db->trans_commit(); //Commit Trans

            $data['success'] = true;
            $data['message'] = 'Data update successfully';
            logging('update role user');
            //$data['rows'] = $table->get($items[$table->pkey]);
        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans

            $data['message'] = $e->getMessage();
            $data['rows'] = $items;
        }

        return $data;

    }

    function destroy() {
        $ci = & get_instance();
        $ci->load->model('administration/role_user');
        $table = $ci->role_user;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try{
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)){
                foreach ($items as $key => $value){
                    if (empty($value)) throw new Exception('Empty parameter');

                    $table->removeItem($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            }else{
                $items = $items;
                if (empty($items)){
                    throw new Exception('Empty parameter');
                }

                $table->removeItem($items);
                $data['rows'][] = array($table->pkey => $items);
                $data['total'] = $total = 1;
            }

            $data['success'] = true;
            $data['message'] = $total.' Data deleted successfully';
            logging('delete role user');

            $table->db->trans_commit(); //Commit Trans

        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans
            $data['message'] = $e->getMessage();
            $data['rows'] = array();
            $data['total'] = 0;
        }
        return $data;
    }


    function html_select_options_roles() {
        try {
            $ci = & get_instance();
            $ci->load->model('administration/roles');
            $table = $ci->roles;

            $user_id = getVarClean('user_id','int',0);
            $role_id = getVarClean('role_id','int',0);

            if(empty($role_id))
                $table->setCriteria('role.role_id NOT IN (SELECT role_id FROM role_user WHERE user_id = '.$user_id.')');
            else
                $table->setCriteria('role.role_id NOT IN (SELECT role_id FROM role_user WHERE user_id = '.$user_id.' AND role_id != '.$role_id.')');

            $items = $table->getAll(0,-1);
            echo '<select>';
            foreach($items  as $item ){
                echo '<option value="'.$item['role_id'].'">'.$item['role_name'].'</option>';
            }
            echo '</select>';
            exit;
        }catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
}

/* End of file Role_user_controller.php */