<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Role_menu_controller
* @version 07/05/2015 12:18:00
*/
class Role_menu_controller {

    function getTreeJson() {

        $ci = & get_instance();
        $ci->load->model('administration/menus');
        $tMenu = $ci->menus;

        $role_id = getVarClean('role_id','int',0);
        $module_id = getVarClean('module_id','int',0);

        $tMenu->setCriteria('mn.module_id = '.$module_id);
        $items = $tMenu->getAll(0,-1,'menu_order','asc');

        $ci->load->model('administration/role_menu');
        $tRoleMenu = $ci->role_menu;

        $data = array();

        foreach($items as $item) {

            $menu_role_item = $tRoleMenu->getItem($role_id, $item['menu_id']);

            $code = '';
            $checked = false;

            if($menu_role_item == null) {
                $code = '0'.'.'.$item['menu_id'];
            }else {
                $code = $menu_role_item->role_id.'.'.$menu_role_item->menu_id;
                $checked = true;
            }

            if( $tMenu->emptyChildren($item['menu_id']) ) {
                $data[] = array(
                            'id' => $item['menu_id'],
                            'parentid' => empty($item['parent_id']) ? 0 : $item['parent_id'],
                            'text' => $item['menu_title'],
                            'value' => $code,
                            'checked' => $checked,
                            'icon' => base_url('images/file-icon.png')
                          );
            }else {
                $data[] = array(
                            'id' => $item['menu_id'],
                            'parentid' => empty($item['parent_id']) ? 0 : $item['parent_id'],
                            'text' => $item['menu_title'],
                            'value' => $code,
                            'checked' => $checked,
                            'icon' => base_url('images/folder-close.png')
                          );
            }

        }

        echo json_encode($data);
        exit;
    }


    function create() {

        $ci = & get_instance();
        $ci->load->model('administration/role_menu');
        $tRoleMenu = $ci->role_menu;

        $role_id = getVarClean('role_id','int',0);

        $items_checked = getVarClean('items_checked','str','');
        $items_unchecked = getVarClean('items_unchecked','str','');

        $data = array('success' => false, 'message' => '');

        try {

            if(empty($role_id)) throw new Exception('Role ID is Empty');

            if(strlen($items_checked) > 0) {
                $tRoleMenu->actionType = 'CREATE';
                $list_checked = explode(',',$items_checked);
                foreach($list_checked as $checked) {
                    $code = explode('.', $checked);
                    if($code[0] == 0)  { //have no role, then insert
                        $tRoleMenu->setRecord(
                            array('role_id' => $role_id,
                                  'menu_id' => $code[1])
                        );
                        $tRoleMenu->create();
                    }
                }
            }


            if(strlen($items_unchecked) > 0) {
                $list_unchecked = explode(',',$items_unchecked);
                foreach($list_unchecked as $unchecked) {
                    $code = explode('.', $unchecked);
                    if($code[0] != 0)  { //have role, then delete
                        //delete data from table
                        $tRoleMenu->deleteItem($code[0], $code[1]);
                    }
                }
            }

            $data['success'] = true;
            $data['message'] = 'Data updated succesfully';
            logging('set role menu');
        }catch(Exception $e) {
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
    }
}

/* End of file Role_menu_controller.php */