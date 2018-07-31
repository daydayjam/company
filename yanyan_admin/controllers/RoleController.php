<?php
/**
 * 角色控制器类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Role;

class RoleController extends Controller {
    
    /**
     * 渲染角色列表
     * @return 
     */
    public function actionList() {
        $fid = $this->getParam('fid');
        $page = $this->getParam('page', 1);
        $record = 20;
        $params = [];
        $params['ac_name'] = '聊天室管理';
        $params['op_name'] = '角色列表';
        $Role = new Role();
        $result = $Role->getList($fid, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 渲染角色详情界面
     * @return 
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $Role = new Role();
        $result = $Role->getInfo($id);
        return $this->render('info', $result);
    }
    
    /**
     * 渲染聊天室创建界面
     * @return 
     */
    public function actionAdd() {
        $params = [];
        $params['fid'] = $this->getParam('fid');
        return $this->render('add', $params, ['ac_name'=>'聊天室管理','op_name'=>'添加角色']);
    }
    
    /**
     * 保存信息
     * @return void
     */
    public function actionSave() {
        $fid = $this->getParam('fid');
        $roleName = $this->getParam('role_name');
        $rdesc = $this->getParam('rdesc');
        $avatar = $this->getParam('avatar');
        $Role = new Role();
        $result = $Role->saveRole($fid, $roleName, $rdesc, $avatar);
        if(!$result) {
            $this->showError($Role);
        }
        $this->showOk();
    }
    
    /**
     * 更新聊天室信息
     * @return void
     */
    public function actionUpdate() {
        $id = $this->getParam('id');
        $roleName = $this->getParam('role_name');
        $rdesc = $this->getParam('rdesc');
        $avatar = $this->getParam('avatar');
        $Role = new Role();
        $result = $Role->updateRole($id, $roleName, $rdesc, $avatar);
        if(!$result) {
            $this->showError($Role);
        }
        $this->showOk();
    }
    
    /**
     * 删除聊天室
     * @return void
     */
    public function actionDel() {
        $ids = $this->getParam('ids');
        $Role = new Role();
        $result = $Role->del($ids);
        if(!$result) {
            $this->showError($Role);
        }
        $this->showOk();
    }
    
    
}

